<?php
/*
 * Synchronize the four composite "Legendary" character titles.
 *
 * Guild Wars awards these automatically when their prerequisite campaign
 * titles are maxed. They are derived state in GWST too: users never enter
 * points for them directly.
 */
if (isset($_SESSION['userid']) && (int)$_SESSION['prefcharid'] > 0) {
    $legendary_rules = [
        'Legendary Cartographer' => [
            'Cartographer (Prophecies)',
            'Cartographer (Factions)',
            'Cartographer (Nightfall)',
        ],
        'Legendary Guardian' => [
            'Protector of Tyria',
            'Protector of Cantha',
            'Protector of Elona',
            'Guardian (Prophecies)',
            'Guardian (Factions)',
            'Guardian (Nightfall)',
        ],
        'Legendary Skill Hunter' => [
            'Skill Hunter (Prophecies)',
            'Skill Hunter (Factions)',
            'Skill Hunter (Nightfall)',
        ],
        'Legendary Vanquisher' => [
            'Vanquisher (Prophecies)',
            'Vanquisher (Factions)',
            'Vanquisher (Nightfall)',
        ],
    ];

    $source_maxed = $con->prepare(
        "SELECT COUNT(*)
         FROM gwstats gs
         INNER JOIN gwtitles gt ON gt.titlenameid = gs.titlenameid
         WHERE gt.titlename = ?
           AND gs.charid = ?
           AND gs.accid = ?
           AND gs.userid = ?
           AND gs.percent >= 100"
    );
    $target = $con->prepare(
        "SELECT gt.titlenameid, gs.stnameid, gs.stname, gs.strank, gs.stpoints
         FROM gwtitles gt
         INNER JOIN gwsubtitles gs ON gs.titlenameid = gt.titlenameid
         WHERE gt.titlename = ?
         ORDER BY gs.strank DESC
         LIMIT 1"
    );
    $upsert = $con->prepare(
        "INSERT INTO gwstats
            (titlenameid, stnameid, titlepoints, currentstrankname, currentstrank,
             percent, gwamm, charid, accid, userid)
         VALUES (?, ?, ?, ?, ?, 100, 0, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            stnameid = VALUES(stnameid),
            titlepoints = VALUES(titlepoints),
            currentstrankname = VALUES(currentstrankname),
            currentstrank = VALUES(currentstrank),
            percent = 100,
            gwamm = 0"
    );
    $remove = $con->prepare(
        "DELETE gs
         FROM gwstats gs
         INNER JOIN gwtitles gt ON gt.titlenameid = gs.titlenameid
         WHERE gt.titlename = ?
           AND gt.autofilled = 1
           AND gt.gwamm = 0
           AND gs.charid = ?
           AND gs.accid = ?
           AND gs.userid = ?"
    );

    foreach ($legendary_rules as $legendary_title => $requirements) {
        $requirements_met = true;

        foreach ($requirements as $required_title) {
            $source_maxed->bind_param(
                "siii",
                $required_title,
                $_SESSION['prefcharid'],
                $_SESSION['prefaccid'],
                $_SESSION['userid']
            );
            $source_maxed->execute();
            $source_maxed->bind_result($maxed_count);
            $source_maxed->fetch();
            $source_maxed->free_result();

            if ((int)$maxed_count !== 1) {
                $requirements_met = false;
                break;
            }
        }

        if ($requirements_met) {
            $target->bind_param("s", $legendary_title);
            $target->execute();
            $target->bind_result($target_id, $subtitle_id, $subtitle_name, $subtitle_rank, $subtitle_points);

            if ($target->fetch()) {
                $target->free_result();
                $upsert->bind_param(
                    "iiisiiii",
                    $target_id,
                    $subtitle_id,
                    $subtitle_points,
                    $subtitle_name,
                    $subtitle_rank,
                    $_SESSION['prefcharid'],
                    $_SESSION['prefaccid'],
                    $_SESSION['userid']
                );
                $upsert->execute();
            } else {
                $target->free_result();
            }
        } else {
            $remove->bind_param(
                "siii",
                $legendary_title,
                $_SESSION['prefcharid'],
                $_SESSION['prefaccid'],
                $_SESSION['userid']
            );
            $remove->execute();
        }
    }

    $source_maxed->close();
    $target->close();
    $upsert->close();
    $remove->close();
}
?>
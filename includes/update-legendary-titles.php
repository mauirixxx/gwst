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
        // target title ID => prerequisite title IDs
        37 => [25, 41, 42],                 // Legendary Cartographer
        38 => [28, 47, 48, 26, 45, 46],    // Legendary Guardian
        39 => [29, 51, 52],                 // Legendary Skill Hunter
        40 => [31, 49, 50],                 // Legendary Vanquisher
    ];

    $source_maxed = $con->prepare(
        "SELECT COUNT(*)
         FROM gwstats gs
         WHERE gs.titlenameid = ?
           AND gs.charid = ?
           AND gs.accid = ?
           AND gs.userid = ?
           AND gs.percent >= 100"
    );
    $target = $con->prepare(
        "SELECT gs.titlenameid, gs.stnameid, gs.stname, gs.strank, gs.stpoints
         FROM gwsubtitles gs
         WHERE gs.titlenameid = ?
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
         WHERE gs.titlenameid = ?
           AND gt.autofilled = 1
           AND gt.gwamm = 0
           AND gs.charid = ?
           AND gs.accid = ?
           AND gs.userid = ?"
    );

    foreach ($legendary_rules as $legendary_title_id => $requirements) {
        $requirements_met = true;

        foreach ($requirements as $required_title_id) {
            $source_maxed->bind_param(
                "iiii",
                $required_title_id,
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
            $target->bind_param("i", $legendary_title_id);
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
                "iiii",
                $legendary_title_id,
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
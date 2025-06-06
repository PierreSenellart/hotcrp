<?php
// api_job.php -- HotCRP job-related API calls
// Copyright (c) 2008-2024 Eddie Kohler; see LICENSE.

class Job_API {
    /** @return JsonResult */
    static function job(Contact $user, Qrequest $qreq) {
        if (($jobid = trim($qreq->job ?? "")) === "") {
            return JsonResult::make_missing_error("job");
        } else if (strlen($jobid) < 24
                   || !preg_match('/\A\w+\z/', $jobid)) {
            return JsonResult::make_parameter_error("job");
        }

        try {
            $tok = Job_Capability::find($jobid, $user->conf);
        } catch (CommandLineException $ex) {
            $tok = null;
        }
        if (!$tok) {
            return JsonResult::make_not_found_error("job");
        }

        $ok = $tok->is_active();
        // XXX is it meaningfully safer to treat inactive tokens as not found?
        $answer = ["ok" => $ok] + (array) $tok->data();
        $answer["ok"] = $ok;
        $answer["update_at"] = $answer["update_at"] ?? $tok->timeUsed;
        return new JsonResult($ok ? 200 : 409, $answer);
    }
}

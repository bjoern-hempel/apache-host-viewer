<?php

/**
 * An apache host viewer php helper class.
 *
 * @author  Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2017-07-21)
 */
class ApacheHostViewer
{
    /**
     * An initializer.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function initialize()
    {   
        /* set timezone */
        date_default_timezone_set('Europe/Berlin');
    }

    /**
     * The apache host viewer controller.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function controller()
    {
        self::initialize();

        /* get some uri specific datas */
        $requestUri    = $_SERVER['REQUEST_URI'];
        $requestedFile = basename($requestUri);

        /* do some uri specific datas */
        switch ($requestedFile) {
            case 'live.json':
                ApacheHostViewer::printJsonLiveValues();
                break;

            case 'update.json':
                ApacheHostViewer::printJsonUpdateStatus();
                break;

            default:
                ApacheHostViewer::printJsonMessageStatus("Unknown requested file \"$requestedFile\".", 'failed');
                break;
        }
    }

    /**
     * Returns the overall status.
     *
     * @version 1.0 (2017-07-23)
     */
    public static function getStatusOverall()
    {
        $statusOverall = 'ok';

        foreach (func_get_args() as $status) {
            if ($status === 'warn' && $statusOverall === 'ok') {
                $statusOverall = $status;
            }

            if ($status === 'critical') {
                $statusOverall = $status;
            }
        }

        return $statusOverall;
    }

    /**
     * Returns the live.json.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getLiveValues()
    {
        $loadStatusCritical = 1;
        $loadStatusWarn     = 0.8; 

        $ramStatusCritical = 95;
        $ramStatusWarn     = 85;

        $hdStatusCritical = 95;
        $hdStatusWarn     = 85;

        /* collect some informations */
        $load         = sys_getloadavg();
        $loadStatus   = 'ok';
        $loadStatus   = self::getStatusOverall($loadStatus, $load[0] >= $loadStatusCritical ? 'critical' : ($load[0] >= $loadStatusWarn ? 'warn' : 'ok'));
        $loadStatus   = self::getStatusOverall($loadStatus, $load[1] >= $loadStatusCritical ? 'critical' : ($load[1] >= $loadStatusWarn ? 'warn' : 'ok'));
        $loadStatus   = self::getStatusOverall($loadStatus, $load[2] >= $loadStatusCritical ? 'critical' : ($load[2] >= $loadStatusWarn ? 'warn' : 'ok'));
        $time         = time();
        $timeFormated = date('Y-m-d H:i:s');
        $appName      = exec('friends-of-bash version apache-host-viewer');
        $osName       = exec('friends-of-bash osName');

        $ramTotal       = intval(exec('free -b | awk \'$1=="Mem:"{print $2}\''));
        $ramUsed        = intval(exec('free -b | awk \'$1=="Mem:"{print $3}\''));
        $ramUsedPercent = round($ramUsed * 100 / $ramTotal, 1);
        $ramStatus      = $ramUsedPercent < $ramStatusWarn ? 'ok' : ($ramUsedPercent < $ramStatusCritical ? 'warn' : 'critical');

        $tasksTotal    = intval(exec('top -bn1 | grep zombie | awk \'{print $2}\''));
        $tasksRunning  = intval(exec('top -bn1 | grep zombie | awk \'{print $4}\''));
        $tasksSleeping = intval(exec('top -bn1 | grep zombie | awk \'{print $6}\''));
        $tasksStopped  = intval(exec('top -bn1 | grep zombie | awk \'{print $8}\''));
        $tasksZombie   = intval(exec('top -bn1 | grep zombie | awk \'{print $10}\''));
        $tasksStatus   = $tasksZombie > 0 ? 'warn' : 'ok';

        exec('df -T -B1 | tail -n +2 | awk \'$2~/^ext[0-9]/{print $1,$7,$2,$3,$4}\'', $hdsResult);
        $hds = array();
        $hdsStatus = 'ok';
        foreach ($hdsResult as $hdResult) {
            $hd = array_combine(array('device', 'mount', 'fs', 'total', 'used'), explode(' ', $hdResult));

            $totalHd       = intval($hd['total']);
            $usedHd        = intval($hd['used']);
            $usedHdPercent = round($usedHd * 100 / $totalHd, 1);
            $hdStatus      = $usedHdPercent < $hdStatusWarn ? 'ok' : ($usedHdPercent < $hdStatusCritical ? 'warn' : 'critical');

            $hdsStatus = self::getStatusOverall($hdsStatus, $hdStatus);

            $hds[$hd['mount']] = array(
                'device'       => $hd['device'],
                'mount'        => $hd['mount'],
                'fs'           => $hd['fs'],
                'total'        => $totalHd,
                'total-gb'     => round($totalHd / 1024 / 1024 / 1024, 1),
                'used'         => $usedHd,
                'used-gb'      => round($usedHd / 1024 / 1024 / 1024, 1),
                'used-percent' => $usedHdPercent,
                'free-percent' => 100 - $usedHdPercent,
                'status'       => $hdStatus,
            );
        }

        return array(
            'timezone'            => date_default_timezone_get(),
            'created-at-formated' => $timeFormated,
            'created-at'          => $time,
            'created-by'          => $appName,
            'os-name-full'        => $osName,
            'load-average'        => array(
                1  => $load[0],
                5  => $load[1],
                15 => $load[2],
            ),
            'hds' => $hds,
            'ram' => array(
                'total'        => $ramTotal,
                'total-mb'     => round($ramTotal / 1024 / 1024, 1),
                'used'         => $ramUsed,
                'used-mb'      => round($ramUsed / 1024 / 1024, 1),
                'used-percent' => $ramUsedPercent,
                'free-percent' => 100 - $ramUsedPercent,
            ),
            'tasks' => array(
                'total'    => $tasksTotal,
                'running'  => $tasksRunning,
                'sleeping' => $tasksSleeping,
                'stopped'  => $tasksStopped,
                'zombie'   => $tasksZombie,
            ),
            'status' => array(
                'overall'  => self::getStatusOverall($loadStatus, $hdsStatus, $ramStatus, $tasksStatus),
                'detailed' => array(
                    'load-average'    => $loadStatus,
                    'space-available' => $hdsStatus,
                    'ram'             => $ramStatus,
                    'task'            => $tasksStatus,
                ),
            ),
        );
    }

    /**
     * Returns a status object including a given message.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getMessageStatus($message, $status = 'ok', Array $array = array())
    {
        $time         = time();
        $timeFormated = date('Y-m-d H:i:s');

        return array_merge(
            $array,
            array(
                'timezone'            => date_default_timezone_get(),
                'created-at-formated' => $timeFormated,
                'created-at'          => $time,
                'status'              => $status,
                'message'             => $message,
            )
        );
    }

    /**
     * Gets the apache-host-viewer update command.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getUpdateCommand()
    {
        /* the apache-host-viewer command name */
        $apacheHostViewerCommand = 'apache-host-viewer';

        $apacheHostViewerUpdateCommandDefault = 'apache-host-viewer '.
                                                '--output-target=/var/www/html/server '.
                                                '--output-name=index '.
                                                '--show-system-info '.
                                                '--show-links '.
                                                '--show-ssl-certificates '.
                                                '--show-domain-list '.
                                                '--create-json '.
                                                '--create-html '.
                                                '--silence';

        $apacheHostViewerUpdateCommand = exec(
            sprintf(
                'cat /etc/crontab | grep -v "^#" | grep "%s" | sed -e "s/^.*%s/%s/g"',
                $apacheHostViewerCommand,
                $apacheHostViewerCommand,
                $apacheHostViewerCommand
            )
        );

        if ($apacheHostViewerUpdateCommand === '') {
            $apacheHostViewerUpdateCommand = $apacheHostViewerUpdateCommandDefault;
        }

        return sprintf('%s %s', 'sudo', $apacheHostViewerUpdateCommand);
    }

    /**
     * Updates the apache host viewer data base.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getUpdateStatus()
    {
        /* the command itself */
        $command = self::getUpdateCommand();

        /* the working directory */
        $cwd = '/opt/friends-of-bash';

        /* some environment variables */
        $env = array();

        /* pipe input */
        $stdin = null;

        /* the pipe config (read input, write stdout and stderr */
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        /* user */
        $userid = posix_geteuid();
        $user   = posix_getpwuid(posix_geteuid());

        /* open the command */
        $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

        /* stdout & stderr & retval */
        $stdout = null;
        $stderr = null;
        $retval = 0;

        /* resource is not available */
        if (!is_resource($process)) {
            $hints = <<<HINTS
<p>
    An error occurred. :(
</p>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS;
        
            return self::getMessageStatus(
                'An error occurred, while trying to do the update. The proc_open ressource could not be opened.',
                'failed',
                array(
                    'command' => $command,
                    'hints'   => $hints,
                )
            );
        }

        /* ressource is available */
        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $retval = proc_close($process);

        /* an error occurred */
        if ($retval > 0) {
            $hints = <<<HINTS
<p>
    The command was not executed successfully.
    Have you allowed the script "apache-host-viewer" to user "%s" (visudo)?
    Please try something like this:
</p>

<pre>
sudo vi /etc/sudoers
</pre>

<pre>
# some scripts www-data is allowed to execute as www-data
www-data ALL=NOPASSWD:/usr/local/bin/apache-host-viewer
</pre>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS;

            return self::getMessageStatus(
                sprintf('The command was not executed successfully and returns the status code %d.', $retval),
                'failed',
                array(
                    'command' => $command,
                    'hints'   => sprintf($hints, $user['name']),
                )
            );
        }

        $hints = <<<HINTS
<p>
    The apache host viewer data base was successfully updated.
</p>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS;

        return self::getMessageStatus(
            'The apache host viewer data base was successfully updated.',
            'success',
            array(
                'command' => $command,
                'hints'   => $hints,
            )
        );
    }

    /**
     * Prints the given data object as json object. Also adds the json header.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function printJson($data)
    {
        header('Content-Type: application/json');

        echo json_encode($data);
    }

    /**
     * Prints the live values.
     *
     * @version 1.0 (2017-07-21)
     */ 
    public static function printJsonLiveValues()
    {
        self::printJson(self::getLiveValues());
    }

    /**
     * Prints the update status.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function printJsonUpdateStatus()
    {
        self::printJson(self::getUpdateStatus());
    }

    /**
     * Prints out the message status.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function printJsonMessageStatus($message, $status = 'ok', Array $array = array())
    {
        self::printJson(self::getMessageStatus($message, $status, $array));
    }
}

/* start the apache host viewer controller */
ApacheHostViewer::controller();


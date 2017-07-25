<?php

namespace AHV;

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
                self::printJsonLiveValues();
                break;

            case 'update.json':
                self::printJsonUpdateStatus();
                break;

            case 'update-library.json':
                self::printJsonUpdateLibrary();
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
        $loadStatus   = self::getStatusOverall(
            $loadStatus,
            $load[0] >= $loadStatusCritical ? 'critical' : ($load[0] >= $loadStatusWarn ? 'warn' : 'ok')
        );
        $loadStatus   = self::getStatusOverall(
            $loadStatus,
            $load[1] >= $loadStatusCritical ? 'critical' : ($load[1] >= $loadStatusWarn ? 'warn' : 'ok')
        );
        $loadStatus   = self::getStatusOverall(
            $loadStatus,
            $load[2] >= $loadStatusCritical ? 'critical' : ($load[2] >= $loadStatusWarn ? 'warn' : 'ok')
        );
        $time         = time();
        $timeFormated = date('Y-m-d H:i:s');
        $appName      = sprintf('%s', exec('friends-of-bash version apache-host-viewer'));
        $osName       = exec('friends-of-bash osName');
        $hostname     = gethostname();

        $ramTotal       = intval(exec('free -b | awk \'$1=="Mem:"{print $2}\''));
        $ramUsed        = intval(exec('free -b | awk \'$1=="Mem:"{print $3}\''));
        $ramUsedPercent = round($ramUsed * 100 / $ramTotal, 1);
        $ramStatus      = $ramUsedPercent < $ramStatusWarn ?
            'ok' :
            ($ramUsedPercent < $ramStatusCritical ? 'warn' : 'critical');

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
            $hdStatus      = $usedHdPercent < $hdStatusWarn ?
                'ok' :
                ($usedHdPercent < $hdStatusCritical ? 'warn' : 'critical');

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
            'created-at-formated' => $timeFormated,
            'created-at'          => $time,
            'created-by'          => $appName,
            'os-name-full'        => $osName,
            'hostname'            => $hostname,
            'timezone'            => date_default_timezone_get(),
            'status' => array(
                'overall'  => self::getStatusOverall($loadStatus, $hdsStatus, $ramStatus, $tasksStatus),
                'detailed' => array(
                    'load-average'    => $loadStatus,
                    'space-available' => $hdsStatus,
                    'ram'             => $ramStatus,
                    'task'            => $tasksStatus,
                ),
            ),
            'load-average'        => array(
                1  => $load[0],
                5  => $load[1],
                15 => $load[2],
            ),
            'hds' => $hds,
            'ram' => array(
                'total'        => $ramTotal,
                'total-gb'     => round($ramTotal / 1024 / 1024 / 1024, 1),
                'used'         => $ramUsed,
                'used-gb'      => round($ramUsed / 1024 / 1024 / 1024, 1),
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
            array(
                'created-at-formated' => $timeFormated,
                'created-at'          => $time,
                'timezone'            => date_default_timezone_get(),
                'status'              => $status,
                'message'             => $message,
            ),
            $array
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

        /* get some user informations */
        $user     = posix_getpwuid(posix_geteuid());
        $username = $user['name'];

        $statusMessages = array(
            'success' => 'The command was executed successfully.',
        );

        $hints = array(
                'command-error' => <<<HINTS
<h3>A command error occurred. :(</h3>

<p>
    The command was not executed successfully.
    Have you allowed the user "$username" to sudo the script "apache-host-viewer" (visudo)?
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
HINTS
        ,
                'success' => <<<HINTS
<h3>Success. :)</h3>

<p>The apache host viewer data base was successfully updated.</p>

<p><a href="/server">Go back to server page.</a></p>
HINTS
        ,
        );

        return array(
            'command'        => sprintf('%s %s', 'sudo', $apacheHostViewerUpdateCommand),
            'statusMessages' => $statusMessages,
            'hints'          => $hints,
        );
    }

    /** 
     * Gets the friends-of-bash update library command.
     *
     * @version 1.0 (2017-07-25)
     */
    public static function getUpdateLibraryCommand($sudoUser)
    {
        /* default command */
        $updateLibraryCommand = 'friends-of-bash update all -y';

        /* get some user informations */
        $user   = posix_getpwuid(posix_geteuid());
        $username = $user['name'];

        $statusMessages = array();

        $hints = array(
                'command-error' => <<<HINTS
<h3>A command error occurred. :(</h3>

<p>
    The command was not executed successfully.
    Have you allowed the user "$username" to sudo (-u $sudoUser) the script "friends-of-bash" (visudo)?
    Please try something like this:
</p>

<pre>
sudo vi /etc/sudoers
</pre>

<pre>
# some scripts www-data is allowed to execute as www-data
www-data ALL=($sudoUser:$sudoUser) NOPASSWD:/usr/local/bin/friends-of-bash
</pre>

<p>
    <a href="/server">Go back to server page.</a>
</p>
HINTS
        ,
                'success' => <<<HINTS
<h3>Success. :)</h3>

<p>The apache host viewer data base was successfully updated.</p>

<p><a href="/server">Go back to server page.</a></p>
HINTS
        ,
        );

        return array(
            'command'        => sprintf('%s %s', 'sudo -u bjoern', $updateLibraryCommand),
            'statusMessages' => $statusMessages,
            'hints'          => $hints,
        );
    }

    /**
     * Update the given command.
     *
     * @version 1.0 (2017-07-25)
     */
    public static function executeCommand($command, Array $statusMessages = array(), Array $hints = array())
    {
        $statusMessages = array_merge(
            array(
                'general-error' => 'An error occurred, while trying to do the update.'.
                    'The proc_open ressource could not be opened.',
                'command-error' => 'The command was not executed successfully and returns the status code %d.',
                'success'       => 'The command was executed successfully.',
            ),
            $statusMessages
        );

        $hints = array_merge(
            array(
                'general-error' => <<<HINTS
<h3>An error occurred. :(</h3>

<p><a href="/server">Go back to server page.</a></p>
HINTS
        ,
                'command-error' => <<<HINTS
<h3>A command error occurred. :(</h3>

<p>The command was not executed successfully.</p>

<p><a href="/server">Go back to server page.</a></p>
HINTS
        ,
                'success' => <<<HINTS
<h3>Success. :)</h3>

<p>The apache host viewer data base was successfully updated.</p>

<p><a href="/server">Go back to server page.</a></p>
HINTS
        ,
            ),
            $hints
        );

        /* the working directory */
        $cwd = '/opt/friends-of-bash';

        /* some environment variables */
        $env = array();

        /* pipe input */
        $stdin = null;

        /* the pipe config (read input, write stdout and stderr */
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        /* open the command */
        $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

        /* stdout & stderr & retval */
        $stdout = null;
        $stderr = null;
        $retval = 0;

        /* resource is not available */
        if (!is_resource($process)) {
            return self::getMessageStatus(
                $statusMessages['general-error'],
                'failed',
                array(
                    'command' => $command,
                    'hints'   => $hints['general-error'],
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
            return self::getMessageStatus(
                sprintf($statusMessages['command-error'], $retval),
                'failed',
                array(
                    'command' => $command,
                    'stdout'  => $stdout,
                    'stderr'  => $stderr,
                    'hints'   => $hints['command-error'],
                )
            );
        }

        return self::getMessageStatus(
            $statusMessages['success'],
            'success',
            array(
                'command' => $command,
                'stdout'  => $stdout,
                'stderr'  => $stderr,
                'hints'   => $hints['success'],
            )
        );
    }

    /**
     * Updates the apache host viewer data base.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getUpdateStatus()
    {
        /* get the command */
        $command = self::getUpdateCommand();

        /* execute command */
        return self::executeCommand($command['command'], $command['statusMessages'], $command['hints']);
    }

    /**
     * Updates the friends of bash libraries.
     *
     * @version 1.0 (2017-07-21)
     */
    public static function getUpdateLibrary()
    {
        $userfile = '.user';

        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        $userfilePath = sprintf('%s/%s/%s', $documentRoot, 'server', $userfile);

        if (!file_exists($userfilePath)) {
            return self::getMessageStatus(
                sprintf(
                    'The userfile "%s" was not found. '.
                    'Within this file have to specify which user should call the friends-of-bash command.',
                    $userfilePath
                ),
                'failed'
            );
        }

        $username = file_get_contents($userfilePath);
        list($username) = preg_split('~[^a-z0-9_-]~i', $username);

        if ($username === '') {
            return self::getMessageStatus(
                sprintf('The userfile "%s" is empty.', $userfilePath),
                'failed'
            );
        }

        $user = posix_getpwnam($username);

        if ($user === false) {
            return self::getMessageStatus(
                sprintf('The user "%s" does not exist on this system.', $username),
                'failed'
            );
        }

        if ($username === 'root') {
            return self::getMessageStatus(
                sprintf('It is not allowed to use the user "%s" within the file "%s".', $username, $userfilePath),
                'failed'
            );
        }

        /* get the command */
        $command = self::getUpdateLibraryCommand($username);

        /* Update all libraries */
        $result = self::executeCommand($command['command'], $command['statusMessages'], $command['hints']);

        $message = $result['status'] === 'success' ?
            'The friends of bash libraries were updated successfully.' :
            'The friends of bash libraries weren\'t updated successfully.';

        /* Get library list */
        exec('friends-of-bash list -s', $applications);

        return self::getMessageStatus(
            $message,
            $result['status'],
            array(
                'update-result' => $result,
                'applications'  => $applications,
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
     * Prints the update library status.
     *
     * @version 1.0 (2017-07-25)
     */
    public static function printJsonUpdateLibrary()
    {
        self::printJson(self::getUpdateLibrary());
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

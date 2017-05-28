# An apache host viewer

An apache host viewer to list all provided host and vhosts on current system.

## A.) Installation

Create a directory and clone the git repository into:

```
user$ mkdir ~/apache-host-viewer && cd ~/apache-host-viewer
user$ git clone git@github.com:bjoern-hempel/apache-host-viewer.git .
```

## 1.) Usage

### 1.1) Show the help dialog

```
user$ ~/apache-host-viewer/bin/apache-host-viewer --help

Usage: bin/apache-host-viewer [options...]
 -h,    --help                    Shows this help.

 -j,    --json                    Shows the output as json and disable the markdown output
        --compress-json           Compress the outputted json.

        --output-target           Writes the result into this target folder.
        --output-name             Set the output name (default is "index").
        --create-markdown         Creates a markdown file (--output-target must be set).
        --create-json             Creates a json file (--output-target must be set).
        --create-html             Creates a html file (--output-target must be set).

 -s,    --show-system-info        Shows system informations.

 -d,    --docker-container        Checks a docker machine instead of the local machine.

        --disable-vhost-parser    Disable the vhost parser.

        --silence                 Disable the output.
```

### 1.2) Simply get all DocumentRoots as markdown markup language

The script uses the DUMP_VHOSTS function from `apachectl` (`apachectl -S`). This requires sudo credentials:

```
user$ sudo ~/apache-host-viewer/bin/apache-host-viewer
```

The markdown result could be:

```
### Projects

- file:///var/www/de/rsm-live/wmbw/start-up-bw/www/html/current/web
  - targets:
    - http://www.start-up-bw.wmbw.rsm-live.de
    - http://start-up-bw.wmbw.rsm-live.de
    - https://www.start-up-bw.wmbw.rsm-live.de
    - https://start-up-bw.wmbw.rsm-live.de
  - ssl: true
  - app: HTML project
  - version: unknown
- file:///var/www/html
  - targets:
    - http://wmbw.rsm-live.de
    - http://www.wmbw.rsm-live.de
    - http://wmbw.rsm-live.de
    - https://www.wmbw.rsm-live.de
    - https://wmbw.rsm-live.de
  - ssl: true
  - app: HTML project
  - version: unknown
- https://www.startupgipfel.de/registration/
  - targets:
    - http://www.start-up-bw.de
    - http://start-up-bw.de
    - http://www.startupbw.de
    - http://startupbw.de
  - ssl: false
  - app: redirection
  - version: not available
- file:///var/www/de/rsm-stage/wmbw/start-up-bw/www/html/current/web
  - targets:
    - http://www.start-up-bw.wmbw.rsm-stage.de
    - http://start-up-bw.wmbw.rsm-stage.de
    - https://www.start-up-bw.wmbw.rsm-stage.de
    - https://start-up-bw.wmbw.rsm-stage.de
  - ssl: true
  - app: TYPO3
  - version: 8.7.1
```

### 1.3) HTML output

TODO..

### 1.4) JSON output

TODO..

### 1.5) Show additional system informations

TODO..

### 1.6) Check docker container instead of local system

TODO..

## B.) License

MIT © [Björn Hempel](https://www.ixno.de)

Have fun! :)

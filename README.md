# An apache host viewer

An apache host viewer to list all provided host and vhosts on current system.

## A.) Installation

Create a directory and clone the git repository into:

```
user$ mkdir ~/apache-host-viewer && cd ~/apache-host-viewer
user$ git clone git@github.com:bjoern-hempel/apache-host-viewer.git .
```

## 1.) Usage

### 1.1) Simply markdown

The script uses the DUMP_VHOSTS function from `apachectl` (`apachectl -S`). This requires sudo credentials:

```
user$ sudo ./apache-host-viewer
```

The markdown result could be:

```
- /var/www/de/rsm-development/bmel/haustierportal/staging/html
  - http://staging.haustierportal.bmel.rsm-development.de
  - https://staging.haustierportal.bmel.rsm-development.de
- /var/www/de/rsm-development/bmel/haustierportal/live/html
  - http://live.haustierportal.bmel.rsm-development.de
  - http://haustier-berater.de
  - http://www.haustier-berater.de
  - https://live.haustierportal.bmel.rsm-development.de
  - https://haustier-berater.de
  - https://www.haustier-berater.de
- /var/www/de/rsm-development/bmel/500li/staging/html
  - http://staging.500li.bmel.rsm-development.de
  - https://staging.500li.bmel.rsm-development.de
- /var/www/de/rsm-live/bienenfuettern/www/html
  - http://www.bienenfuettern.rsm-live.de
  - http://bienenfuettern.rsm-live.de
  - http://www.bienenfuettern.de
  - http://bienenfuettern.de
  - http://www.bienen-fuettern.de
  - http://bienen-fuettern.de
  - http://www.bienenfüttern.de
  - http://bienenfüttern.de
  - http://www.bienen-füttern.de
  - http://bienen-füttern.de
  - https://www.bienenfuettern.rsm-live.de
  - https://bienenfuettern.rsm-live.de
  - https://www.bienenfuettern.de
  - https://bienenfuettern.de
  - https://www.bienen-fuettern.de
  - https://bienen-fuettern.de
  - https://www.bienenfüttern.de
  - https://bienenfüttern.de
  - https://www.bienen-füttern.de
  - https://bienen-füttern.de
- /var/www/html
  - http://bmel.rsm-development.de
- /var/www/de/rsm-development/bmel/500li/live/html
  - http://live.500li.bmel.rsm-development.de
  - http://500landinitiativen.de
  - http://www.500landinitiativen.de
  - https://live.500li.bmel.rsm-development.de
  - https://500landinitiativen.de
  - https://www.500landinitiativen.de
```

### 1.1) JSON output

TODO..

## B.) License

MIT © [Björn Hempel](https://www.ixno.de)

Have fun! :)

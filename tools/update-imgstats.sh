#!/bin/bash

dir=`dirname "$0"`
d="$1"

yesterday=`date -d '1 day ago' +%Y-%m-%d`
out="$d/html/cache/stats/stats-$yesterday.txt"

"$dir"/count-pics.sh "$d/logs/nginx_access.log.1.gz" cache > "$out"

chgrp logs "$out"
chmod a+r "$out"

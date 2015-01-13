#!/bin/bash

f="$1"
d="$2"

gunzip -c "$f" | sed 's!.*"\(GET\|HEAD\) \([^ ?]*\)[^ ]* HTTP/1\.[01]".*!\2!' | egrep "^/$d/.*\.(jpg|png|gif)" | sort | uniq -c | sort -nr -k 1

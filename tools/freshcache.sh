#!/bin/bash

if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
	echo 1>&2 "USAGE: $0 [cachelist] [targetdir]"
	echo 1>&2 "  <cachelist> is a file containing two columns, first the url, seond the file name."
	echo 1>&2 "              per default, stdin is used."
	echo 1>&2 "  <targetdir> is the directory to write the files to."
	echo 1>&2 "              per default, the current directory is used."
	exit 1
fi

cachelist="${1:--}"
targetdir="${2:-.}"

if [ "$cachelist" != '-' ] && [ ! -f "$cachelist" ] && [ ! -h "$cachelist" ]; then
	echo 1>&2 "file not found: $cachelist"
	exit 2
fi

if [ ! -d "$targetdir" ]; then
	echo 1>&2 "dir not found: $targetdir"
	exit 2
fi

tmp="$targetdir/freshcache-$$.tmp"

cat "$cachelist" | while read u n stuff;  do
	if [ -z "$u" ]; then continue; fi
	if [ -z "$n" ]; then continue; fi

	f="$targetdir/$n"
	
	if [ -f "$tmp" ]; then
		rm -f "$tmp"
	fi
	
	wget --quiet -nc -U "wikipedia.de/freshcache" -O "$tmp" "$u"
	
	x="$?"
	
	if [ "$x" -eq 0 ]; then 
	
		if [ ! -s "$tmp" ]; then #check if we actually got data
			echo 1>&2 "wget fetched nothing from <$u>!"
			continue
		else #if we got data, override the old file in an atomic way
			mv -f "$tmp" "$f"
			touch "$f"
			chmod a+r "$f"
		fi
	
	elif [ "$x" -eq 8 ]; then #404 not found
		echo 1>&2 "wget could not find the page <$u>!"
	elif [ "$x" -eq 4 ]; then #bad url, or host not found
		echo 1>&2 "wget failed to fetch data from <$u>!"
	else #some other error code
		echo 1>&2 "wget returned code $x when fetching <$u> to <$f>!"
	fi
done 

if [ -f "$tmp" ]; then
	rm -f "$tmp"
fi

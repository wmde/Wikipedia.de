#!/bin/bash

domain="$1"
query="$2"

echo -n "$query" $'\t'
time ( 
curl -s "http://$domain/" > /dev/null; 
curl -s "http://$domain/suggest.php?lang=de&search=$query" > /dev/null; 
curl -s "http://$domain/go.php?s=x&l=de&e=wikipedia&q=$query" > /dev/null; 
) 
# Wikipedia.de

This code is used on wikipedia.de. It provides functionalities to run 
search queries and suggests page titles based on search strings.

It can be configured to retrieve featured content and banners from a 
remote MediaWiki installation.

## Installation

Copy ```inc/config.sample.inc.php``` to ```inc/config.inc.php```. See 
inline comments for configuration options.

Run

    docker-compose up
    
You can reach the local version of this project at [http://localhost:8085/](http://localhost:8085/)

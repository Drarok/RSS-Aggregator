RSS Aggregator
---------------

# What is RSS Aggregator?

RSS Aggregator is a set of PHP scripts that fetches, parses, and 
reconstitutes a set of RSS feeds into a single feed, maintaining
the correct date order of the posts.

# Requirements

You will need PHP5 at a minimum. I'm not sure if there are any
particular versions, but at present I'm developing under PHP 5.3.1
on Windows and PHP 5.2.6-3ubuntu4.5.

You will also require one of the SQLite extensions either compiled
in, enabled in your php.ini, or available to dl().

# Why did you start this project?

I'm using a tool which takes RSS input, but has a woeful interface
to add/remove feeds, and would prefer to simply edit a config file
in Vim than use their clunky web interface.

Also, it gave me another reason to write a little compatibility layer
for SQLite so that either SQLite3 or SQLite2 could be used almost
transparently.

# ...Why?!

Because I'm strange like that.

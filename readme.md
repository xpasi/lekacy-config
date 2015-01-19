Legacy/Config

Copyright Pasi Rajala <p@prr.fi>

a simple but powerfull configuration manager for PHP

Supports loading all files from a directory or loading a single file from any location.

Configuration values are saved in JSON format and configuration files need the .json extension.

Uses namespaces defined by the filename from which the key was loaded from.
eg. loading a json file named "example.json" with content of {'key':'value'} results
in a configuration key of example.key

The system keeps track of the file where the value has been last loaded from and
they can easily be saved back to the same file or to a different directory.
Namespaces apply to saving also, so that the first part of the key is always the filename.

When setting new values, use a namespace like "app".
Setting a single depth key (without the namespace), will throw an error.

Namespacing allows you to overwrite values in a meaningfull matter.
For example if you have a "multisite" system and you want to load sane defaults first
and then overwrite them with site specific values, you could do:
Load a config /app/config/configuration.json for system wide database settings.
Then load a config /app/sites/config/configuration.json

Both will set the namespace (the first part of the key) to "configuration"
and any values already set by the global config will be replaced with the values of the site specific file.


USAGE:

Config::get($key)
Params: $key is the configuration key
Description: Returns the value ofa configuration key.

Config::set($key,$value)
Params: $key is the configuration key and $value is the configuration value
Sets a configuration key/value pair. If key already exists, it is overwriten.

Config::load($location)
Params: $location is either a directory or a full file path.
Description: Loads configuration files. If $location is a directory, then system autoloads all .json files from the location.

Config::reset()
Description: Resets the whole system

Config::save($key,$location)
Params: $key is the configuration key to be saved. $location is the directory where it should be saved.
Description:
Saves a file containing the $key. Note that the first part of the key is the namespace (the filename).
So if $key is "database.username" and $location is '/my/app/' then the configuration file will be writen in /my/app/database.json


Why JSON?
I selected JSON as the configuration format, because it's human readable and the syntax needs
to be valid JSON for it to parse, eg. no chance of syntax broken config files.
Also JSON is easy to convert from/to PHP array, which makes the saving of configuration files very easy and robust.

Unlike other possible methods:
- Using straight PHP (eg arrays) would of been fast and simple to read in, but very hard to save.
- Serialaized PHP would of been easy to parse and easy to save, but very hard to edit manually.
- INI format, easy to read in, again hard to write out.
- XML ... I hate XML!

Only downside I can think of is, that JSON doesn't support commenting, so the meaning of configuration values need to be commented elsewhere.
Thou implementing a save method that supports existing comments in any format would be a real pain and require ugly code.

As this system allows using keys of any "depth", use this to your advantage of setting up a context for the values.
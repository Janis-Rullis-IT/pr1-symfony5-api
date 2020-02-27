# Why use docker?

* [When and Why to Use Docker? (linode.com)](https://www.linode.com/docs/applications/containers/when-and-why-to-use-docker/#benefits-of-docker)
* [Why use docker? 3 reasons from a development perspective (dev.to)](https://dev.to/geshan/why-use-docker-3-reasons-from-a-development-perspective-2jh3)
* [I am a Developer: why should I use Docker ? (blog.octo.com)](https://blog.octo.com/i-am-a-developer-why-should-i-use-docker/)

## - Provide the exact enviroment on all target systems

Avoid environment differences - the software might use some specific functionality only available in the defined software.
Results might differ, in case if the enviroment uses something else. Even the same program can have different results on diffrent OSes.

This is **very important for server setups** - developer's enironment must be exact as the servers otherwise the outcome may be destructive.

## - To keep the target system (server or user's computer) untouched

and avoid impacting it with this setup.
Docker is an isolated and independent enviroment that won't affect the current OSes setup. It can easily be removed like nothing happend.

## - A project can be set without it

Don't want to setup Docker? Good, You still can set this project without it.

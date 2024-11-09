# plibv4\process\timeshare

## Introduction

The purpose of Timeshare is to implement some kind of parallel programming using (pure) PHP, which is in itself single threaded.
While it can't work magic - a long running task like sha1_file on a large file will still block - it works fine with tasks that can be split into small pieces. Like listening to stdin (using `stream_nonblocking(STDIN, true)`) and walking a directory at seemingly the same time.

Special emphasis was put on having a truly object oriented approach. 

## Components

Currently, Timeshare consists of three main components:

* `Task`, which is an interface that defines how a task should be implemented.
* `Scheduler`, which is a scheduler that switches between various instances of `Task` and is an instance of `Task` as well (which allows to create a process hierarchy).
* `Timeshare`, which is currently the only implementation of `Scheduler`.
* `Strategy`, which is an interface that defines the actual strategy of task switching. Currently, only `RoundRobin` is implemented.
* `TimeshareObserver`, an interface of which one or several implementations can be added to `Timeshare` and which are called on specific events regarding `Tasks` instances (such as a process getting started)

## Task

`Task` is an interface which defines several methods. Please note that their names got the prefix `__ts`, which is to denote that they should not be called by any other component than an instance of `Scheduler`.

* `__tsStart` - this function is called once a process, which is added to `Timeshare`, gets started. As some time may pass between `Timeshared::__construct()` and `Timeshared::__tsStart()`, this allows to run preparatory work right at the beginning of the process, such as opening a file.
* `__tsLoop` - this is the main component of any instance of `Timeshared`. Run whatever code is necessary here. Try not to run any loops or long running functions within `__tsLoop`, such as `sha1_file`. By returning `true` or `false`, you signal `Timeshare` that it should continue to run your task or finish.
* `__tsPause`, `__tsResume` - not yet implemented.
* `__tsFinish` - `__tsFinish` is called after `__tsLoop` returned `false`. This allows you to properly end a task, such as closing a file or removing temporary data.
* `__tsTerminate` - this function is called if your task is cancelled by the outside. You are allowed to defer termination by returning `false`, which can be used to finish tasks using `__tsLoop`. As long as `__tsTerminate` returns `false`, `__tsLoop` is called.
* `__tsKill` - `__tsKill` basically means: end, now. `__tsKill` should not run large tasks.
* `__tsError` - a process which throw an exception would cause the whole program to come down. Therefore, Exceptions are caught and given to `__tsError` along with the step at which they happened (START, LOOP, FINISH, TERMINATE).

## Examples

### Copy a file

* `__tsStart` - open source, open target (temporary file).
* `__tsLoop` - copy a part of the file from source to target. Return `false` when done
* `__tsFinish` - close file, rename temporary file to target.
* `__tsTerminate` - delete temporary file
* `__tsError` - handle errors (file not readable and so on)

### Serving a client, closed during shutdown

* `__tsStart` - send greeting to client
* `__tsLoop` - check for client data or send server data
* `__tsFinish` - close connection
* `__tsTerminate` - put client notification into buffer, return `true` if buffer is empty.

#### Error handling

We imagine copying a file as an example, throwing exceptions on things that go wrong. As a first example, we assume that opening the file goes wrong:

* `__tsStart` - tries to open file, but fails and throws an Exception
    * `__tsError` with `Scheduler::START` is called
    * observers are called (`TimeshareObserver::onError`)

As a second example, let's assume an exception is thrown during the copy process:

* `__tsLoop` - open file, but fails and throws an Exception
    * `__tsError` with `Scheduler::LOOP` is called
    * observers are called (`TimeshareObserver::onError` with `Scheduler::LOOP`)

As a third example, let's assume that the scheduler terminates the process prematurely, but the process fails:
* `__tsTerminate` - tries to close file, but fails and throws exception
    * `__tsError` with `Scheduler::TERMINATE` is called
    * observers are called (`TimeshareObserver::onError` with `Scheduler::TERMINATE`)

As a fourth example, the process finishes normally, but then fails:
* `__tsFinish` - tries to close file, but fails and throws exception
    * `__tsError` with `Scheduler::FINISH` is called
    * observers are called (`TimeshareObserver::onError` with `Scheduler::FINISH`)
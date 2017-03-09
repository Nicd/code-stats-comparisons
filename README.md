This repository contains benchmarking programs for comparing results of Code::Stats with
other systems. More specifically, it contains two comparison programs, written in PHP and Python,
that implement a similar service to Code::Stats. They can be used to compare the service's
performance in load tests.

The `ws/` directory contains the Ratchet websocket server implementation that is used to
emulate Phoenix channels.

All of the programs require PostgreSQL 9.5+ where the database should be stored and ZeroMQ
to talk to Ratchet.

The `tsung-configs/` directory contains `tsung` configuration files used for the tests.

All of the code in this repository is licensed under the BSD 3-clause licence (check the LICENCE
file). The code is part of Mikko Ahlroth's master's thesis.

#!/bin/bash

bin/console rabbitmq-producer &
bin/console rabbitmq-consumer appli_a &
bin/console rabbitmq-consumer appli_b &
bin/console rabbitmq-consumer appli_c &

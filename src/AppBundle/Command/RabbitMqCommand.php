<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RabbitMqCommand extends Command {

	protected function configure() {
		$this->setName('rabbitmq');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		
		
		
		$output->writeln('RabbitMQ demo');
		
	}

}

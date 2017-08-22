<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqCommand extends Command {

	protected $rabbitmq_host;

	protected $rabbitmq_port;

	protected $rabbitmq_user;

	protected $rabbitmq_password;

	public function __construct() {
		$this->rabbitmq_host = 'rabbitmq';
		$this->rabbitmq_port = 5672;
		$this->rabbitmq_user = 'guest';
		$this->rabbitmq_password = 'guest';
		
		parent::__construct();
	}


	protected function configure() {
		$this->setName('rabbitmq');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('RabbitMQ demo');
		
		$producer_connection = new AMQPStreamConnection($this->rabbitmq_host, $this->rabbitmq_port, $this->rabbitmq_user, $this->rabbitmq_password);
		$channel = $producer_connection->channel();
		
		// TODO: see https://github.com/php-amqplib/php-amqplib/blob/master/demo/amqp_publisher_with_confirms.php
		
		$consummer_connection = new AMQPStreamConnection($this->rabbitmq_host, $this->rabbitmq_port, $this->rabbitmq_user, $this->rabbitmq_password);
		
		
	}

}

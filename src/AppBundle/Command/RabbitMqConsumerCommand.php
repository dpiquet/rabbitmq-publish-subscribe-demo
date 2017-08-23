<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Ressources:
 * https://www.rabbitmq.com/tutorials/tutorial-three-php.html
 * https://stackoverflow.com/questions/15342340/trouble-with-rabbitmq-fanout-exchange
 *
 * Chaque consumer doit créer sa propre queue et l'associer au fanout du producer
 */


class RabbitMqConsumerCommand extends Command {

	protected $rabbitmq_host;

	protected $rabbitmq_port;

	protected $rabbitmq_user;

	protected $rabbitmq_password;

	protected $exchange;

	protected $queue_name;

	public function __construct() {
		$this->rabbitmq_host = 'rabbitmq';
		$this->rabbitmq_port = 5672;
		$this->rabbitmq_user = 'guest';
		$this->rabbitmq_password = 'guest';
		$this->exchange = 'demo-exchange';
		
		parent::__construct();
	}


	protected function configure() {
		$this
			->setName('rabbitmq-consumer')
			->addArgument('queue_name', InputArgument::REQUIRED, 'queue name')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->queue_name = $input->getArgument('queue_name');
		$queue_name = $input->getArgument('queue_name');
		
		$output->writeln(sprintf('RabbitMQ demo - Starting consumer on %s exchange', $this->exchange));
		
		$consumer_connection = new AMQPStreamConnection($this->rabbitmq_host, $this->rabbitmq_port, $this->rabbitmq_user, $this->rabbitmq_password);
		$consumer_channel = $consumer_connection->channel();
		
		// publish confirm mode
		$consumer_channel->confirm_select();
		
		
		$consumer_channel->exchange_declare(
			$this->exchange, //exchange
			'fanout', //type
			false,  //passive (don't check if exchange with same name already exists)
			true, //durable (survive service restarts)
			false //autodelete delete channel when channel closed
		);
		
		// Declare queue (creates if not exists)
		$consumer_channel->queue_declare(
			$this->queue_name,
			false, //passive
			true, //durable
			false, //exclusive
			false, //autodelete
			false //nowait
		);
		
		$consumer_channel->queue_bind($this->queue_name, $this->exchange);
		
		$output->writeln(sprintf('[consumer %s] ready to process messages', $this->queue_name));
		
		// Consume all messages in queue
		while($msg_recv = $consumer_channel->basic_get($this->queue_name)) {
			$output->writeln(sprintf('got message %s !', $msg_recv->body));
			
			// ack message
			$consumer_channel->basic_ack($msg_recv->delivery_info['delivery_tag']);
		}
		
		$output->writeln('quit !');
		
		$consumer_channel->close();
		$consumer_connection->close();
	}

}

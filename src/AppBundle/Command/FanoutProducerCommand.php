<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Objectif:
 *  obtenir un système producer / multiple consumers où tous les consumers recoivent le message et notifient le producer
 *  Attention, la queue doit vivre même lorsque l'application est morte pour ne pas perdre de messages
 *  un timeout doit être défini pour notifier le producer de l'absence prolongée d'un consumer
 *
 * Ressources:
 *
 * https://www.rabbitmq.com/tutorials/tutorial-three-php.html
 * https://stackoverflow.com/questions/15342340/trouble-with-rabbitmq-fanout-exchange
 */


class FanoutProducerCommand extends Command {

	protected $rabbitmq_host;

	protected $rabbitmq_port;

	protected $rabbitmq_user;

	protected $rabbitmq_password;

	protected $exchange;

	protected $msg_delay;

	public function __construct() {
		$this->rabbitmq_host = 'rabbitmq';
		$this->rabbitmq_port = 5672;
		$this->rabbitmq_user = 'guest';
		$this->rabbitmq_password = 'guest';
		$this->exchange = 'demo-exchange';
		
		$this->msg_delay = 1;
		
		parent::__construct();
	}


	protected function configure() {
		$this->setName('fanout-producer');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln(sprintf('RabbitMQ demo - Starting producer on %s exchange', $this->exchange));
		
		$producer_connection = new AMQPStreamConnection($this->rabbitmq_host, $this->rabbitmq_port, $this->rabbitmq_user, $this->rabbitmq_password);
		$producer_channel = $producer_connection->channel();
		
		// ACK handler
		$producer_channel->set_ack_handler(
			function(AMQPMessage $message) use ($output) {
				$output->writeln(sprintf('[producer] ACK (%s)', $message->body));
			}
		);
		
		// NACK handler
		$producer_channel->set_nack_handler(
			function(AMQPMessage $message) use ($output) {
				$output->writeln(sprintf('[producer] NACK (%s)', $message->body));
			}
		);
		
		// ???
		$producer_channel->set_return_listener(
			function ($replyCode, $replyText, $exchange, $routingKey, AMQPMessage $message) {
				print "[producer] Message returned with content " . $message->body . PHP_EOL;
			}
		);
		
		// publish confirm mode
		$producer_channel->confirm_select();
		
		$producer_channel->exchange_declare(
			$this->exchange, //exchange
			'fanout', //type
			false,  //passive (don't check if exchange with same name already exists)
			true, //durable (survive service restarts)
			false //autodelete delete channel when channel closed
		);
		
		
		while($producer_channel) {
			$msg_body = sprintf('producer_msg [%s]', time());
			$message = new AMQPMessage($msg_body, array('content_type' => 'text/plain'));
			$producer_channel->basic_publish($message, $this->exchange);
			$producer_channel->wait_for_pending_acks();
			sleep($this->msg_delay);
		}
		
		$producer_channel->close();
		$producer_connection->close();
	}

}

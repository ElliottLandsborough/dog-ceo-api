<?php

namespace commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use models\Statistic;

class GetCountriesCommand extends Command
{
    use LockableTrait;

    private $stats;

    public function __construct()
    {
        parent::__construct();
        $stats = new Statistic;
        $this->stats = $stats;
    }

    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('app:countries')

        // the short description shown while running "php bin/console list"
        ->setDescription('Gets the countries when possible.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('Gets the countries...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $visits = $this->stats->getAllVisitsWithNoCountry();
        $visitsCount = $visits->num_rows;
        $doneIps = [];
        $output->writeln("<info>$visitsCount entries to update.</info>");
        foreach ($this->stats->getAllVisitsWithNoCountry() as $visit) {
            $ID = $visit['id'];
            $ip = $visit['ip'];
            if ($ip && strlen($ip)) {
                $details = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip={$ip}"));
                if ($details->geoplugin_countryCode) {
                    $country = $details->geoplugin_countryCode;
                    if (!in_array($ip, $doneIps)) {
                        $sql = "UPDATE visits SET country = '$country' WHERE ip = '$ip';";
                        $this->stats->query($sql);
                        $doneIps[] = $ip;
                        $output->writeln("<info>Entry set was updated for ip $ip</info>");
                    }
                }
                sleep(1); // sleep for half a second after an api call;
            }
        }
        $output->writeln('<info>Finished</info>');

        $this->release();
    }
}

<?php

namespace App\Command;

use App\Parser\Parser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'parser:run',
    description: 'Run stores parser',
    hidden: false
)]
class ParserCommand extends Command
{
    public function __construct(
        protected HttpClientInterface $client,
        protected SerializerInterface $serializer,
        protected Filesystem $filesystem,
        protected int $pagesLimit,
        protected string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parser = new Parser($this->client, $this->serializer, $this->filesystem, $this->pagesLimit, $this->projectDir);

        try {
            $errors = $parser->run();
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $output->writeln($error);
                }
            }

            $output->writeln('Parsing ended');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function configure(): void
    {
        $this->setDescription('Run stores parser');
    }
}
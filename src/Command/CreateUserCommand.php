<?php

namespace App\Command;

use App\Entity\Colleague;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserCommand extends Command
{
    private $em,$validator;
    public function __construct(EntityManagerInterface $entityManagerInterface,ValidatorInterface $validator)
    {
        $this->em = $entityManagerInterface;
        $this->validator = $validator;
        parent::__construct();
    }

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-colleague';

    /**
     * Configureing the console command
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create a colleague!')
            ->addArgument('colleague-name', InputArgument::REQUIRED, 'Colleague name')
            ->addArgument('colleague-email', InputArgument::REQUIRED, 'Colleague Email')
            ->addArgument('colleague-note', InputArgument::OPTIONAL, 'Colleague Note');
    }
    /**
     * Execution of the commands
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $colleague = new Colleague();
        $io = new SymfonyStyle($input, $output);
        $colleagueName = $input->getArgument('colleague-name');
        $colleagueEmail = $input->getArgument('colleague-email');
        $colleagueNote = $input->getArgument('colleague-note');
        if($colleagueName == '' || $colleagueEmail == '') {
            $io->warning("Required two argument");
            $io->note("Try to get help: php bin/console app:create-colleague --help");
            return 0;
        }
        if ($colleagueName) {
            $io->note(sprintf('Hi %s!', $colleagueName));
        }

        $colleague->setName($colleagueName);
        $colleague->setEmail($colleagueEmail);
        if(isset($colleagueNote) && !empty($colleagueNote)) {
            $colleague->setNotes($colleagueNote);
        }

        try {
            $errors = $this->validator->validate($colleague);
            if (count($errors) == 0) {
                $this->em->persist($colleague);
                $this->em->flush();
    
                $io->success("Colleague Created!");
                $io->table(
                    ['ID','Name','Email','Note'],
                    [
                        [ $colleague->getId(), $colleagueName, $colleagueEmail,$colleagueNote ]
                    ],
                );
            } else {
                foreach ($errors as $key => $error) {
                    $io->error($error->getPropertyPath() .": " . $error->getMessage());
                }
            }
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $io->success($msg);
        }
        return 0;
    }
}

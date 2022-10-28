<?php

namespace Networking\InitCmsBundle\Command;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'networking:initcms:clear-logs', description: 'Clear DB log entries')]
class ClearLogCommand extends \Symfony\Component\Console\Command\Command
{

    protected static $defaultName = 'networking:initcms:clear-logs';

    private ManagerRegistry $em;

    public function __construct(ManagerRegistry $em, $pageClass, string $name = null)
    {
        $this->em = $em;
        $this->pageClass = $pageClass;
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = $this->em->getConnection();

        $sql = 'SELECT id FROM page';
        $result = $conn->executeQuery($sql)->fetchAllAssociative();

        foreach ($result as $item){
            $sql = "SELECT id FROM ext_log_entries";
            $sql .= " WHERE object_id = :objectId";
            $sql .= " AND object_class = :objectClass";
            $sql .= " ORDER BY version DESC LIMIT 1";

            $params = [':objectId' => $item['id'], ':objectClass' => $this->pageClass];



            $stmt = $conn->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $logEntry = $stmt->executeQuery()->fetchOne();

            $sql = 'DELETE FROM ext_log_entries WHERE id != :logId AND object_id = :objectId AND object_class = :objectClass';

            $stmt = $conn->prepare($sql);

            foreach ([':logId' => $logEntry, ':objectId' => $item['id'], ':objectClass' =>$this->pageClass] as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $count = $stmt->executeStatement();
            $output->writeln(sprintf('<info>%s entries deleted for page %s</info>', $count, $item['id']));
        }

        $sql = "SELECT object_id FROM ext_log_entries";
        $sql .= " WHERE action = :remove";
        $sql .= " AND object_class = :objectClass";

        $stmt = $conn->prepare($sql);

        foreach ([':remove' => 'remove', ':objectClass' => $this->pageClass] as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $deletedPages = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($deletedPages as $deletedPage){
            $sql = 'DELETE FROM ext_log_entries WHERE object_id = :objectId AND object_class = :objectClass';

            $stmt = $conn->prepare($sql);

            foreach ([':objectId' => $deletedPage['object_id'], ':objectClass' =>$this->pageClass] as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $count = $stmt->executeStatement();
            
            $output->writeln(sprintf('<info>%s entries deleted for removed page %s</info>', $count, $deletedPage['object_id']));
        }

        return 0;
    }
}
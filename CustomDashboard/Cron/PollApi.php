<?php
namespace Testing\CustomDashboard\Cron;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class PollApi
{
    protected $curl;
    protected $logger;
    protected $resourceConnection;
    protected $maxAttempts = 10;

    public function __construct(
        Curl $curl,
        LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        $pendingTasks = $this->getPendingTasks();

        foreach ($pendingTasks as $task) {
            $jobId = $task['job_id'];
            $attempts = $task['attempts'];

            if ($attempts >= $this->maxAttempts) {
                $this->markTaskFailed($jobId);
                continue;
            }

            $status = $this->checkApiStatus($jobId);

            if ($status === 'complete') {
                $this->markTaskComplete($jobId);
            } else {
                $this->incrementAttempts($jobId, $attempts);
            }
        }
    }

    private function checkApiStatus($jobId)
    {
        $url = 'https://api.example.com/status/' . $jobId;
        $this->curl->get($url);
        $response = $this->curl->getBody();
        $data = json_decode($response, true);
        return $data['status'] ?? 'unknown';
    }

    private function getPendingTasks()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('your_api_tasks');
        $select = $connection->select()
            ->from($tableName)
            ->where('status IN (?)', ['pending', 'processing']);
        return $connection->fetchAll($select);
    }

    private function incrementAttempts($jobId, $currentAttempts)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('your_api_tasks');
        $connection->update(
            $tableName,
            ['attempts' => $currentAttempts + 1],
            ['job_id = ?' => $jobId]
        );
    }

    private function markTaskComplete($jobId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('your_api_tasks');
        $connection->update(
            $tableName,
            ['status' => 'complete', 'updated_at' => date('Y-m-d H:i:s')],
            ['job_id = ?' => $jobId]
        );
    }

    private function markTaskFailed($jobId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('your_api_tasks');
        $connection->update(
            $tableName,
            ['status' => 'failed'],
            ['job_id = ?' => $jobId]
        );
    }
}
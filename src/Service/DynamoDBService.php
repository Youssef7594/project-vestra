<?php

namespace App\Service;

use Aws\Sdk;
use Aws\DynamoDb\DynamoDbClient;
use Ramsey\Uuid\Uuid;

class DynamoDBService
{
    private $dynamoDb;
    private $tableName;

    public function __construct()
    {
        $sdk = new Sdk([
            'region'   => 'eu-north-1',  // Remplacer ici directement la région si nécessaire
            'version'  => 'latest',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        $this->dynamoDb = $sdk->createDynamoDb();
        $this->tableName = $_ENV['AWS_DYNAMODB_TABLE'];
    }

    public function saveProject(array $projectData): void
    {
        $this->client->putItem([
            'TableName' => $this->tableName,
            'Item' => $this->marshalItem($projectData),
        ]);
    }

    private function marshalItem(array $item): array
    {
        $marshalled = [];
        foreach ($item as $key => $value) {
            if (is_numeric($value)) {
                $marshalled[$key] = ['N' => (string) $value];
            } else {
                $marshalled[$key] = ['S' => (string) $value];
            }
        }
        return $marshalled;
    }

    /* stockage des commentaire dans le cloud  */

    public function addComment(string $projectId, string $userId, string $content)
    {
        $commentId = Uuid::uuid4()->toString();
        $createdAt = (new \DateTime())->format(DATE_ATOM);

        $this->dynamoDb->putItem([
            'TableName' => $this->tableName,
            'Item' => [
                'id'         => ['S' => $commentId],
                'project_id' => ['S' => $projectId],
                'user_id'    => ['S' => $userId],
                'content'    => ['S' => $content],
                'created_at' => ['S' => $createdAt],
            ],
        ]);

        return $commentId;
    }

    public function getCommentsByProject(string $projectId)
    {
        $result = $this->dynamoDb->query([
            'TableName' => $this->tableName,
            'IndexName' => 'project_id-created_at-index', // Remplacez par le nom exact de votre GSI
            'KeyConditionExpression' => 'project_id = :project_id',
            'ExpressionAttributeValues' => [
                ':project_id' => ['S' => $projectId],
            ],
            'ScanIndexForward' => false, // Pour trier du plus récent au plus ancien
        ]);
    

        return $result['Items'] ?? [];
    }


        public function deleteComment(string $commentId): void
    {
        $this->dynamoDb->deleteItem([
            'TableName' => $this->tableName,
            'Key' => [
                'id' => ['S' => $commentId]
            ],
        ]);
    }

    public function getCommentById(string $commentId): ?array
    {
        $result = $this->dynamoDb->getItem([
            'TableName' => $this->tableName,
            'Key' => [
                'id' => ['S' => $commentId]
            ],
        ]);

        return $result['Item'] ?? null;
    }

}

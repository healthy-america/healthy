<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Helper;

use DateTime;
use Firebase\JWT\JWT;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

class DbHelper
{

    /**
     * @var ResourceConnection $resource
     */
    protected ResourceConnection $resource;

    /**
     * @var SistecreditoOrderLog $sistecreditoOrderLog
     */
    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $_logger;

    /**
     * @var AdapterInterface $connection
     */
    protected AdapterInterface $connection;

    public function __construct(
        LoggerInterface    $logger,
        ResourceConnection $resource
    )
    {
        $this->_logger = $logger;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    public function createSistecreditoOrderLog($message, &$sistecreditoOrderLog)
    {
        $date = new DateTime();
        $sistecreditoOrderLog->date = $date->format("Y-m-d H:i:s");
        $data = [
            [
                'date' => $sistecreditoOrderLog->date,
                'order_id' => $sistecreditoOrderLog->orderId,
                'total_order' => $sistecreditoOrderLog->totalOrder,
                'action' => $sistecreditoOrderLog->action,
                'request_token' => $sistecreditoOrderLog->requestToken,
                'jwt' => $sistecreditoOrderLog->jwt,
                'request_url' => $sistecreditoOrderLog->requestUrl,
                'request' => $sistecreditoOrderLog->request,
                'response' => $sistecreditoOrderLog->response,
                'transaction_id' => $sistecreditoOrderLog->transactionId,
                'credit_number' => $sistecreditoOrderLog->creditNumber,
            ]
        ];
        $tableName = $this->resource->getTableName('sistecredito_order_log');
        $this->connection->insertMultiple($tableName, $data);
        $this->_logger->debug($message, ["orderId" => $sistecreditoOrderLog->orderId]);
    }

    public function getSistecreditoOrderLog($sistecreditoModelOrderLog)
    {
        $tableName = $this->resource->getTableName('sistecredito_order_log');
        $tokenGenerated = GatewayActions::TOKEN_GENERATED;
        $sql = "SELECT * FROM $tableName WHERE order_id = {$sistecreditoModelOrderLog->orderId}
                      AND jwt = '{$sistecreditoModelOrderLog->jwt}'
                      AND action = '{$tokenGenerated}'
                      AND request_token IS NOT NULL ORDER BY date DESC";
        $result = $this->connection->fetchAll($sql);

        return $result[0] ?? null;
    }

    public function filterInputArray($type, $definition = null) {
        return filter_input_array($type, $definition);
    }

    public function validUrlRedirect($url){

        // Use get_headers() function
        $headers = @get_headers($url);

        // Use condition to check the existence of URL
        if($headers && strpos( $headers[0], '200')) {
            $status = true;
        }
        else {
            $status = false;
        }

        // Display result
        return $status;
    }

    public function fileGetContents($fileName)
    {
        return file_get_contents($fileName);
    }

    public function decodeJwt($token,$key,$alg){
        return JWT::decode($token, $key, $alg);
    }

    public function getObjectManager(): ObjectManager
    {
        return ObjectManager::getInstance();
    }

    public function getShopDomainSsl()
    {
        return Tools::getShopDomainSsl(true, true);
    }

    public function encodeJwt($jwtPayload, $jwtKey, $algorithm): string
    {
        return JWT::encode($jwtPayload, $jwtKey, $algorithm);
    }
}

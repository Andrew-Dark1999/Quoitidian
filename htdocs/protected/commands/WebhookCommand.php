<?php

/**
 * Class WebhookCommand
 *
 * @author Aleksandr Roik
 */
class WebhookCommand extends ConsoleCommand
{

    /**
     * Инициализация вебхука и его отправка
     */
    public function actionInit($copy_id, $action, $request_data = null)
    {
        echo 'Starting "Webhook.Send"' . PHP_EOL;

        $webhookModelList = WebhookModel::model()->with(['method', 'action'])->findAll(
            'copy_id = :copy_id AND action.action_slug = :action',
            [
                ':copy_id' => $copy_id,
                ':action'  => $action,
            ]
        );

        if (!$webhookModelList) {
            echo 'Webhooks not found' . PHP_EOL . 'Done' . PHP_EOL;

            return;
        }

        // Обработка найденых вебхуков
        foreach ($webhookModelList as $webhookModel) {
            if (!$webhookModel->url) {
                continue;
            }

            $method = $webhookModel->method()->method_slug;
            $b = $this->sendWebhook($webhookModel->url, $method, json_decode($request_data, true));

            $msg = "Send request. #{$webhookModel->webhook_id}, method: '{$method}', URL: {$webhookModel->url}, request_data: {$request_data}";

            $log = Yii::getLogger();
            $log->log($msg, $b ? CLogger::LEVEL_INFO : CLogger::LEVEL_ERROR, 'webhook-init');
            $log->flush();

            echo '   ' . $msg . ' - ' . ($b ? 'success' : 'error') . PHP_EOL;
        }

        echo 'Done' . PHP_EOL;
    }

    /**
     * Отправляет вебхук
     *
     * @param $url
     * @param $method
     * @param $request_data
     * @return bool
     */
    private function sendWebhook($url, $method, array $request_data = null)
    {
        /* @var Curl $curl */
        $curl = Yii::app()->curl;
        $curl->setConfigItem('opt_maxredirs', 5);

        switch ($method){
            case WebhookMethodModel::METHOD_GET:
                $curl->sendGet($url);
                break;
            case WebhookMethodModel::METHOD_POST:
                $curl->sendPost($url, $request_data);
                break;
        }

        return $curl->statusSend();
    }
}

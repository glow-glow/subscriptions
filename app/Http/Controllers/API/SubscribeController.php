<?php


namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Rubric;
use Response;
use App\Models\Subscribe;
use Cassandra\Exception\ValidationException;
use SimpleXMLElement;

class SubscribeController extends Controller
{
    /**
     * Подписка пользователя по email на id рубрику
     *
     * @param $rubric_id
     * @param $email
     *
     * @return Response
     * @throws Exception
     *
     * @Rest\Post("subscribe/{id}/user/{email}")
     */
    public function subscribe($rubric_id, $email){
        $status_code = 200;
        // Если нету подписчика в базе, добавляем
        $subscribe = Subscribe::firstOrCreate(array('email' => $email));
        try{
            // Ошибка на не существующую рубрику
            $rubric = Rubric::findOrFail($rubric_id);
        }catch(ValidationException $e){
            $e = 404;
        }

        $rubric->subscribes()->detach($subscribe->id);
        $rubric->subscribes()->attach($subscribe->id);

        return Response::json(array(
            'status_code' => $status_code,
        ));
    }
    /**
     * Удаление подписки по ID & email
     *
     * @param $rubric_id
     * @param $email
     *
     * @return Response
     * @Rest\Delete("subscribe/{rubric_id}/user/{email}")
     */
    public function deleteSubscribe($rubric_id, $email){
        // Проверка есть ли email  таблице
        $subscribe = Subscribe::firstOrCreate(array('email' => $email));
        try{
            // Ошибка на не существующую рубрику
            $rubric = Rubric::findOrFail($rubric_id);
        }catch(ValidationException $e) {
            $e = 404;
        }
        //Удаляем свзяь
        $rubric->subscribes()->detach( $subscribe->id );
        $status_code = 200;
        return Response::json(array(
            'status_code' => $status_code,
        ));
    }
    /**
     * Удаление всех подписок по email
     *
     * @param $email
     *
     * @return Response
     * @Rest\Delete("subscriptions/user/{email}")
     */
    public function deleteSubscribes($email){
        $subscribe = Subscribe::firstOrCreate(array('email' => $email));
        $subscribe->rubrics()->detach();
        $status_code = 200;
        return Response::json(array(
            'status_code' => $status_code,
        ));
    }
    /**
     * Отображение всех подписок пользователя
     *
     * @param Request $request
     * @param $email
     * @throws UnauthorizedHttpException
     *
     * @return Response json
     * @Rest\Get("subscriptions/user/{email}")
     */
    public function subscriptionsUser(Request $request, $email)
    {
        // Request параметр limit - ограничивающий выдачу
        $limit = ((int)$request->limit)? : 5;
        // Request параметр offset - указывающий страницу
        $offset = ((int)$request->offset)? : null;
        // Request параметр отвечающий за выдачу ответа в xml
        $xml = ($request->xml)? : false;

        $subscriptions = Subscribe::firstOrCreate(array('email' => $email));
        $rubrics = $subscriptions->rubrics()->where('subscribe_id', $subscriptions->id)->paginate($limit, ['*'], 'offset', $offset);
        $rubricsArray = $rubrics->toArray();
        // Извлекаем последний элемент pivot
        foreach ($rubricsArray["data"] as $key => $value){
            array_pop($rubricsArray["data"][$key]);
        }

        $requestXml = ($xml)? '&xml=true': null;
        $rubricsArray['next_page_url'] .= ($rubricsArray['next_page_url'])? '&limit=' . $limit . $requestXml: null;
        $rubricsArray['prev_page_url'] .= ($rubricsArray['prev_page_url'])? '&limit=' . $limit . $requestXml: null ;
        $status_code = 200;
        $responseArray = [
            'status_code' => $status_code,
            'data'   => $rubricsArray["data"],
            'pagination' => [
                'total'         => $rubricsArray['total'],
                'per_page'      => $rubricsArray['per_page'],
                'current_page'  => $rubricsArray['current_page'],
                'last_page'     => $rubricsArray['last_page'],
                'next_page_url' => $rubricsArray['next_page_url'],
                'prev_page_url' => $rubricsArray['prev_page_url'],
            ]
        ];
        // Проверка каким способом отдавать данные
        $response = ($xml)? $this->array_to_xml($responseArray) : Response::json($responseArray);
        return $response;
    }
    /**
     * Преобразование массива в xml
     * @param $data
     * @param null $xml_data
     * @return xml|bool|string
     */
    private function array_to_xml ( $data, &$xml_data = null) {
        if (!isset($xml_data)) $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key;
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
        return $xml_data->asXML();
    }

}

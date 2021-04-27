<?php

namespace Larabricks\OutputBuilder;

use Larabricks\Enums\ErrorStrings;
use Larabricks\Enums\InputType;
use Symfony\Component\HttpFoundation\Response;

class OutputBuilder
{


    public static int $code;
    public static array|null $data;


    /**
     * @param array|null $dto
     * @param string $InputType - the type of input provided
     * @return Response
     */
    public static function build( array $dto = null, string $InputType = InputType::LIST ): Response
    {
        $data = OutputBuilder::_filter(OutputBuilder::$data ?? null, $dto, $InputType);
        return ( OutputBuilder::$code >= 200 && OutputBuilder::$code < 300 )
            ? OutputBuilder::_buildSuccess($data)
            : OutputBuilder::_buildError();
    }

    /**
     * @param array|null $dto
     * @param string $InputType - the type of input provided
     * @return Response
     */
    public static function buildPaginated( array $dto = null, string $InputType = InputType::LIST ): Response
    {
        $data = OutputBuilder::$data ?? null;
        if ( !isset($data) ) return [];
        $data['data'] = OutputBuilder::_filter($data['data'], $dto, $InputType);
        return ( OutputBuilder::$code >= 200 && OutputBuilder::$code < 300 )
            ? OutputBuilder::_buildSuccess($data)
            : OutputBuilder::_buildError();
    }

    /**
     * make a successfully response
     * @param array $data
     * @return Response
     */
    private static function _buildSuccess( array $data ): Response
    {
        return response($data, OutputBuilder::$code);
    }

    /**
     * make an error response
     * @return Response
     */
    private static function _buildError(): Response
    {
        $code = OutputBuilder::$code;
        $message = ['code' => $code, 'description' => ErrorStrings::get($code)];
        return response($message, $code);
    }


    /**
     * filter the input data based on passed Dto
     * @param array|null $data
     * @param array|null $dto
     * @param string $InputType - the type of input provided
     * @return array|null
     */
    public static function _filter( array|null $data, array $dto = null, string $InputType = InputType::LIST ): array|null
    {
        if ( $InputType === InputType::ITEM ) $data = [ $data ];
        if ( !isset($dto) ) return $data;
        if ( !isset($data) ) return [];
        $result = [];
        foreach ($data as $item) {
            $result[] = OutputBuilder::_filterArray($dto, $item ?? []);
        }
        if ( $InputType === InputType::ITEM ) $result = $result[0];
        return $result;
    }

    /**
     * recursive function that filter multiple level of the provided
     * array based on the provided dto
     * @param array $dto - to filter the array $data
     * @param array $data - array to filter
     * @return array - filtered array
     */
    private static function _filterArray( array $dto, array $data ): array
    {
        // if is an empty array then return all items
        if ( empty($dto) ) return $data;
        // else
        $array = array_intersect_key($data, $dto);
        foreach ( $array as $key => $value ) {
            // recursive call
            if ( is_array($value) ) $array[$key] = OutputBuilder::_filterArray($dto[$key], $value);
        }
        return $array;
    }

}

<?php

namespace App;

class Router extends \League\Route\Router
{
    public function generateUri(string $routeName, array $params, array $options = []): string
    {
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#']);
        
        $routeData = $this->routeParser->parse(
            $this->parseRoutePath(
                $this->getNamedRoute($routeName)->getPath()
            )
        );
        $routeData = array_pop($routeData);
        
        $route = [];
        foreach ($routeData as $key => $data) {
            if ($key % 2 == 0) { // часть пути
                $route[] = $data;
            } else { // placeholder
                $key = $data[0];
                $keyPattern = $data[1];
                if (!isset($params[$key])) {
                    array_pop($route);
                    break;
                }
                $route[] = $this->encode($params[$key], $options['isRaw'] ?? false);
                unset($params[$key]);
            }
        }
        $url = ltrim(implode('', $route));
        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query . $anchor;
        }
        return $url;
    }
    
    protected function encode($val, $isRaw = false)
    {
        if ($isRaw) {
            return $val;
        }

        return is_scalar($val) ? rawurlencode($val) : null;
    }
}

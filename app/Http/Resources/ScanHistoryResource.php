<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $route = $request->routeIs('api.v1.*') ? 'api.v1.scan.history.image' : 'scan.history.image';

        return [
            ...parent::toArray($request),
            'image_url' => route($route, ['history' => $this->id]),
        ];
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KomerceController extends Controller
{
    private $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

    public function searchDestination(Request $request)
    {
        $keyword = $request->query('keyword');
        if (!$keyword) {
            return response()->json(['data' => []]);
        }

        $response = Http::withHeaders([
            'key' => config('services.komerce.key')
        ])->get("{$this->baseUrl}/destination/domestic-destination", [
            'search' => $keyword,
            'limit' => 20,
            'offset' => 0
        ]);

        $data = $response->json();
        
        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => $data['meta']['message'] ?? 'Gagal menghubungi API Komerce'
            ], $response->status());
        }

        // Map the results to match the expected format in the frontend if needed
        // Frontend expects id and name. The new API returns id and name (or label).
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = array_map(function($item) {
                // Ensure name exists for the frontend (which uses dest.name)
                $item['name'] = $item['label'] ?? ($item['name'] ?? '');
                return $item;
            }, $data['data']);
        }

        return response()->json($data);
    }

    public function cost(Request $request)
    {
        $request->validate([
            'destination' => 'required', // komerce_destination_id
            'weight' => 'required|numeric',
            'courier' => 'required|string', // JNE, POS, TIKI etc
        ]);

        $response = Http::withHeaders([
            'key' => config('services.komerce.key')
        ])->asForm()->post("{$this->baseUrl}/calculate/domestic-cost", [
            'origin' => config('services.komerce.origin_id'),
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier
        ]);

        $data = $response->json();

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => $data['meta']['message'] ?? 'Gagal menghubungi API Komerce'
            ], $response->status());
        }

        // The new API returns data as a flat array of services:
        // [ { "name":"JNE", "code":"jne", "service":"REG", "cost":54000, "etd":"3 day" }, ... ]
        // The frontend expects the old RajaOngkir structure:
        // { results: [ { code: "jne", costs: [ { service: "REG", cost: [ { value: 54000, etd: "3 day" } ] } ] } ] }
        
        if (isset($data['data']) && is_array($data['data'])) {
            $services = $data['data'];
            $costs = array_map(function($item) {
                return [
                    'service' => $item['service'] ?? '',
                    'description' => $item['description'] ?? '',
                    'cost' => [
                        [
                            'value' => $item['cost'] ?? 0,
                            'etd' => $item['etd'] ?? ''
                        ]
                    ]
                ];
            }, $services);

            $mappedResult = [
                'code' => $request->courier,
                'name' => count($services) > 0 ? $services[0]['name'] : $request->courier,
                'costs' => $costs
            ];

            return response()->json([
                'data' => [
                    'results' => [$mappedResult]
                ]
            ]);
        }

        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Models\Asset;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    public function register(Request $request){
        try {
            $request->validate([
               'name' => 'required|string',
               'condition' => 'required|string',
               'purchase_date' => 'date_format:Y-m-d'
            ]);

            //insert new user
            $asset = Asset::create([
                'name' => $request->name,
                'condition' => $request->condition,
                'purchase_date' => $request->purchase_date,
                'price' => $request->price,
                'location' => $request->location,
                'description' => $request->description
            ]);
            

            return ResponseFormatter::success([
                'asset' => $asset
            ],'Asset Created');
        } catch (\Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong', 
                'error' => $error,
            ], 'Asset Creating Failed', 500);
        }
    }

    public function update(Request $request, $id){
        $asset = Asset::findOrFail($id);
        $asset->update($request->all());

        return ResponseFormatter::success($asset, 'Asset Updated');
    }

    public function delete($id){
        $asset = Asset::find($id);
        
        if(!$asset){
            return ResponseFormatter::error([
                'message' => 'something went wrong'
            ], 'update failed', 500);
        }
        $asset->delete();
        return ResponseFormatter::success($asset, 'Asset Deleted');
    }

    public function updatePhoto(Request $request, $id){
        $validator = Validator::make($request->all(),[
            'file' => 'required|image|max:2048'
        ]);
        
        if($validator->fails()){
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Update photo fails', 401);
        }

        if($request->file('file')){
            //simpan foto ke database(url photo disimpan)
            $file = $request->file->store('assets/asset', 'public');
            
            //ambil barang berdasarkan id
            $asset = Asset::find($id);
            if(!isset($asset)){
                return ResponseFormatter::error([
                    'message' => 'asset not found'
                ],'Update Asset Photo Failed', 500);
            }
            $asset->picture_path = $file;
            $asset->update();
        }

        return ResponseFormatter::success(
            [$file], 'File successfully uploaded'
        );
    }
}

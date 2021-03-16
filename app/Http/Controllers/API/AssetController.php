<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Asset;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{   
    //todo perbaiki fungsi asset all
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $sort = $request->input('sort');


        if($sort){
            if($sort == 'terbaru'){
                $asset = DB::table('assets')->orderByDesc('purchase_date')->paginate($limit);
            }else if($sort == 'terlama'){
                $asset = DB::table('assets')->orderBy('purchase_date')->paginate($limit);
            }else if($sort == 'kondisi'){
                $asset = DB::table('assets')->orderBy('condition')->paginate($limit);
            }

            return ResponseFormatter::success($asset, 'Data list barang berhasil diambil');
        }

        if($id){
            $asset = Asset::find($id);

            if($asset){
                return ResponseFormatter::success($asset, 'Data Asset berhasil diambil');
            }else{
                return ResponseFormatter::error(
                    null, 'Data asset tidak ada', 404
                );
            }
        }

        //cari berdasarkan nama
        $asset = Asset::query();
        if($name){
            $asset->where('name', 'like', '%'. $name . '%');
        }


        return ResponseFormatter::success(
            $asset->paginate($limit),
            'Data list barang berhasil diambil'
        );
    }

    public function register(Request $request){
        try {
            $request->validate([
               'name' => 'required|string',
               'condition' => 'required|string',
               'purchase_date' => 'date_format:Y-m-d',
               'image' => 'file|image|mimes:png,jpg|max:5120'   
            ]);
            $user = Auth::user();
            $image = $request->file('image');
            if($image){                
                $imageName = time(). '.'.$image->extension();
                $image = $image->storeAs('uploads/assets', $imageName, 'public');
            }
            //insert new user
            $asset = Asset::create([
                'user_id'=> $user->id,
                'name' => $request->name,
                'condition' => $request->condition,
                'purchase_date' => $request->purchase_date,
                'price' => $request->price,
                'location' => $request->location,
                'description' => $request->description,
                'picture_path' => ($image) ? $image : null,
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
        try{
            $request->validate([
                'name' => 'required|string',
                'condition' => 'required|string',
                'purchase_date' => 'date_format:Y-m-d',
                'image' => 'file|image|mimes:png,jpg|max:5120' 
            ]);
            
            $asset = Asset::find($id);
            
            if($asset){
                $asset->update($request->all());
                if($asset->picture_path != null){
                    Storage::disk('public')->delete($asset->picture_path);
                }
                $image = $request->file('image');
                if($image){
                    $imageName = time(). '.'.$image->extension();
                    $image = $image->storeAs('uploads/assets', $imageName, 'public');
                }
                $asset->picture_path = ($image) ? : $asset->picture_path;
                $asset->update();
                return ResponseFormatter::success($asset, 'Asset updated');
            }else{
                ResponseFormatter::error(null, 'Asset not found', 404);
            }
        
        }catch (\Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong', 
                'error' => $error,
            ], 'Asset Creating Failed', 500);
        }
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

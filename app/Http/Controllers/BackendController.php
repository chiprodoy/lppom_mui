<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use MF\Controllers\ApiResponse;
use MF\Controllers\BreadCrumb;
use MF\Controllers\DataTable;
use MF\Controllers\Page;
use MF\Controllers\PageMenu;
use MF\Controllers\ResponseCode;

class BackendController extends Controller
{
    use PageMenu,DataTable,BreadCrumb,ApiResponse;
    public $modelRecords;
    public $indexURL;
    public $editURL;
    public $deleteURL;
    public $updateURL;
    public $createURL;
    public $storeURL;
    public $showURL;
    public $RECORD;
    public $PUTMETHOD='PUT';
    public $DESTROYMETHOD='DESTROY';
    public $POSTMETHOD='POST';
    public $titleOfCreatePage='Tambah Data';
    public $viewNameOfCreatePage='components.viho.crud.create';
    public $titleOfShowPage='Detail';
    public $viewNameOfShowPage='components.viho.crud.show';
    public $titleOfEditPage='Edit';
    public $viewNameOfEditPage='components.viho.crud.edit';
    public $titleOfIndexPage;
    public $viewNameOfIndexPage='components.viho.crud.index';
    public $newData;
    public $showWith=[];
    public $recordId;
    public $modName;
    public $createResult;
    public $errorMsg;

    public function __construct()
    {
        $this->menuModel=\App\Models\Menu::class;
        $this->getBackEndMenu();
        $this->BREADCRUMB_ITEM=[
            new Page('Home',route('dashboard'))
        ];

        //$this->modName=$this->modelRecords;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->CURRENT_PAGE=new Page($this->titleOfIndexPage,route($this->indexURL));
        $this->setBreadCrumb();
        if(view()->exists('admin.'.$this->modName.'.crud.index')){
            return view('admin.'.$this->modName.'.crud.index',get_object_vars($this));
        }else{
            return view($this->viewNameOfIndexPage,get_object_vars($this));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->CURRENT_PAGE=new Page($this->titleOfCreatePage,route($this->createURL));
        $this->setBreadCrumb();
        if(view()->exists('admin.'.$this->modName.'.crud.create')){
            return view('admin.'.$this->modName.'.crud.create',get_object_vars($this));
        }else{
            return view($this->viewNameOfCreatePage,get_object_vars($this));
        }

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uid)
    {
        $this->RECORD=$this->modelRecords::where('uuid',$uid)->first();
        if(Route::has($this->indexURL)){
            $this->indexPage=new Page($this->titleOfIndexPage,route($this->indexURL));
        }else{
            $this->indexPage=new Page($this->titleOfIndexPage,route_from($this->indexURL));

        }
        array_push($this->BREADCRUMB_ITEM,$this->indexPage);
        if(Route::has($this->indexURL)){
            $this->CURRENT_PAGE=new Page($this->titleOfShowPage,route($this->showURL,$uid));
        }else{
            $this->CURRENT_PAGE=new Page($this->titleOfShowPage,route_from($this->showURL,$this->RECORD));
        }
        $this->setBreadCrumb();
        $this->recordId=$uid;
        return view($this->viewNameOfShowPage,get_object_vars($this));
    }

        /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uid)
    {
        //$this->RECORD=$this->modelRecords::where('uuid',$uid)->first();
        $this->setRecord($uid);
        $this->indexPage=new Page($this->titleOfIndexPage,route_from($this->indexURL,$this->RECORD));
        array_push($this->BREADCRUMB_ITEM,$this->indexPage);
        $this->CURRENT_PAGE=new Page($this->titleOfEditPage,'#');
        $this->setBreadCrumb();
        if(view()->exists('admin.'.$this->modName.'.crud.edit')){
            return view('admin.'.$this->modName.'.crud.edit',get_object_vars($this));
        }else{
            return view($this->viewNameOfEditPage,get_object_vars($this));
        }
    }
    public function setRecord($uid){
        $this->RECORD=$this->modelRecords::where('uuid',$uid)->first();
    }

    public function insertRecord($request){
        $this->setNewData($request);
        if(strpos($this->createURL,'http'===false)){
            $this->createURL=route_from($this->createURL);
        }
        try
        {
            $this->createResult=$this->modelRecords::create($this->newData);
            return $this->output('success',$request,'Data Berhasil Disimpan',$this->createURL);
        }
        catch(QueryException $e)
        {
            $this->errorMsg='Data Gagal Disimpan '.$e->getMessage();
            dd($this->errorMsg);
            Log::error($e);
            if(env('APP_DEBUG')) return $this->output('error',$request,$e->getMessage(),$this->createURL);
            else return $this->output('error',$request,'Data Gagal Disimpan',$this->createURL);

        }

    }

    public function output($type,$request,$message,$redirectURL=null){
        if(Route::has($redirectURL)) $redirectURL=route($redirectURL);
        if($type=='success'){
            return $this->iSuccess($this->createResult,$request,$redirectURL,$message);
        }else{
            if(env('APP_DEBUG')) return $this->iError($request,$redirectURL,ResponseCode::ERROR,$message);

        }

    }
    /*

    */
    public function updateRecord($request,$uid){
        try
        {
           $this->setRecord($uid);
            $this->setNewData($request);
            //$record=$this->modelRecords::where('uuid',$uid);
           // $updated=$record->update($this->newData);
           $updated=$this->RECORD->update($this->newData);

            return $this->iSuccess($updated,$request,route_from($this->editURL,$this->RECORD),'Data Berhasil Diupdate');
        }
        catch(QueryException $e)
        {
            Log::error($e);
            if(env('APP_DEBUG')) return $this->iError($request,route_from($this->editURL,$this->RECORD),ResponseCode::ERROR,$e->getMessage());
            else return $this->iError($request,route_from($this->editURL,$this->RECORD),ResponseCode::ERROR,'Data Gagal Diupdate');

        }

    }
    /*
    *
    */
    public function setNewData($request){
        $m=new $this->modelRecords;
        $resultPath=null;
        foreach($m->getFillable() as $k => $v){
            $this->newData["$v"]=$request->$v;
            //if($m::$formFields[$v]['type']==\App\View\Components\Viho\Form\InputFile::class && $request->file($v)){
            if($m::$formFields[$v]['type']==\App\View\Components\Viho\Form\InputFile::class){

                if(method_exists($this,'uploadMyFile')){
                    if($request->hasFile($v)){
                        $resultPath=$this->uploadMyFile($request->file($v));
                    }else{
                        if($this->RECORD)
                        $resultPath=$this->RECORD->getAttributes()[$v];
                    }
                        $this->newData["$v"]=$resultPath;
                }
            }

        }
/*         foreach($this->modelRecords::$formFields as $k =>$v){
            $field=$v['field'];
            $this->newData["$field"]=$request->$field;
        } */
    }

       /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uid,Request $request)
    {
        try
        {
            $deleted=$this->modelRecords::where('uuid',$uid)->delete();

            return $this->iSuccess($deleted,$request,$this->indexURL,'Data Berhasil Diupdate');
        }
        catch(QueryException $e)
        {
            if(env('APP_DEBUG')) return $this->error($request,$this->indexURL,ResponseCode::ERROR,$e->getMessage());
            else return $this->error($request,$this->indexURL,ResponseCode::ERROR,'Data Gagal Diupdate');

        }

    }
    public function viewRecord(){
        $this->modelRecords=$this->modelRecords::orderBy('id','desc');
        $this->modelRecords->paginate($this->RECORD_PER_PAGE);
    }


}



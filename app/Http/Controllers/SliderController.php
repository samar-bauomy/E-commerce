<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Slider;
class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addslider()
    {
        return view('admin.addslider');
    }
    public function sliders()
    {
        $sliders = Slider::get();

        return view('admin.sliders')->with('sliders',$sliders);
    }

   

   
    public function saveslider(Request $request)
    {
        
        $this->validate($request, ['description_one'=>'required' ,
                                   'description_two'=>'required' ,
                                   'slider_image'=>'image|nullable|max:1999']);

                                   
                                    if($request->hasFile('slider_image')){
                                            
                                        // 1 :get filename with ext
                                        $fileNameWithExt = $request->file('slider_image')->getClientOriginalName();
                            
                                        //2 : get just file name
                                        $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                            
                                        //3 :get  just extension 
                                        $extension =$request->file('slider_image')->getClientOriginalExtension();
                            
                                        //4 :file name to store
                                        $fileNameToStore =$fileName.'_'.time().'.'.$extension;
                            
                                        //upload image
                            
                                        $path =$request->file('slider_image')->storeAs('public/slider_images',$fileNameToStore);
                                    }
                                    else{
                                        $fileNameToStore ='noimage.jpg';
                                    }
                            
                                    $slider = new slider();
                            
                                    $slider->description1=$request->input('description_one');
                                    $slider->description2=$request->input('description_two');
                                    $slider->slider_image=$fileNameToStore;
                                    $slider->status =1;
                                    // if($request->input('product_status')){
                                    //     $product->product_status =1;
                                    // }else{
                                    //     $product->product_status =0;
                                    // }
                            
                                  $slider->save();
                                  return redirect('/sliders')->with('status' , 'The  slider has been saved succssefully' );          
                                                                     
       
    }

   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_slider($id)
    {
        $slider =Slider::find($id);

        return view('admin.editslider')->with('slider',$slider);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateslider(Request $request, $id)
    {
        $this->validate($request, ['description_one'=>'required' ,
                                   'description_two'=>'required' ,
                                   'slider_image'=>'image|nullable|max:1999']);

                                   $slider =slider::find($id);
                            
                                   $slider->description1=$request->input('description_one');
                                   $slider->description2=$request->input('description_two');
                                  
                                   $slider->status =1;  
                                   
                                   if($request->hasFile('slider_image')){
                
                                    // 1 :get filename with ext
                                    $fileNameWithExt = $request->file('slider_image')->getClientOriginalName();
                        
                                    //2 : get just file name
                                    $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                        
                                    //3 :get  just extension 
                                    $extension =$request->file('slider_image')->getClientOriginalExtension();
                        
                                    //4 :file name to store
                                    $fileNameToStore =$fileName.'_'.time().'.'.$extension;
                        
                                    //upload image
                        
                                    $path =$request->file('slider_image')->storeAs('puplic/slider_images',$fileNameToStore);
                                    $old_image =Slider::find($request->input('id'));
                        
                                    if($slider->slider_image != 'noimage.jpg'){
                                        Storage::delete('public/slider_images/'.$slider->slider_image);
                                    }
                                    $slider->slider_image =$fileNameToStore;
                            }
                            $slider->update();
                            return redirect('/sliders')->with('status' , 'The slider has been updated succssefully' );
    }

  
    public function deleteslider($id)
    {
        $slider =Slider::find($id);

        if($slider->slider_image != 'noimage.jpg'){
            Storage::delete('public/slider_images/'.$slider->slider_image);
        }

        $slider->delete();
        return redirect('/sliders')->with('status' , 'The  slider has been deleted succssefully' );
   
    }

    public function activate_slider($id)
    {
       $slider=Slider::find($id);

       $slider->status=1;

       $slider->update();

       return redirect('/sliders')->with('status' , 'The slider status has been activated succssefully' );
        
    }
    public function unactivate_slider($id)
    {
       $slider=Slider::find($id);

       $slider->status=0;

       $slider->update();

       return redirect('/sliders')->with('status' , 'The  slider status has been unactivated succssefully' );
        
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\redirect;
use App\Product;
use App\Category;
use App\Cart;
use Session;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function addproduct()
    {
        $categories = Category::All()->pluck('category_name','category_name');
        return view('admin.addproduct')->with('categories', $categories);
    }


   

    public function saveproduct(Request $request)
    {

        $this->validate($request, ['product_name'=>'required' ,
                                   'product_price'=>'required' ,
                                   'product_image'=>'image|nullable|max:1999']);
       
       if($request->input('product_category')){
        if($request->hasFile('product_image')){
                
            // 1 :get filename with ext
            $fileNameWithExt = $request->file('product_image')->getClientOriginalName();

            //2 : get just file name
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

            //3 :get  just extension 
            $extension =$request->file('product_image')->getClientOriginalExtension();

            //4 :file name to store
            $fileNameToStore =$fileName.'_'.time().'.'.$extension;

            //upload image

            $path =$request->file('product_image')->storeAs('public/product_images',$fileNameToStore);
        }
        else{
            $fileNameToStore ='noimage.jpg';
        }

        $product = new Product();

        $product->product_name =$request->input('product_name');
        $product->product_price =$request->input('product_price');
        $product->product_category =$request->input('product_category');
        $product->product_image =$fileNameToStore;
        $product->status =1;
        // if($request->input('product_status')){
        //     $product->product_status =1;
        // }else{
        //     $product->product_status =0;
        // }

      $product->save();
      return redirect('/addproduct')->with('status' , 'The ' .  $request->input('product_name') .' product has been saved succssefully' );
       }else{
        return redirect('/addproduct')->with('status1' , 'Do select the category please');
       }

      
    }
    public function products()
    {
        $products = Product::get();
        return view('admin.product')->with('products' , $products);
    }

  
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editproduct($id)
    {
        $categories = Category::All()->pluck('category_name','category_name');
        $product = Product::find($id);

        return view('admin.editproduct')->with('product' , $product)->with('categories' ,$categories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateproduct(Request $request, $id)
    {
        $this->validate($request, ['product_name'=>'required' ,
                                   'product_price'=>'required' ,
                                   'product_image'=>'image|nullable|max:1999']);

        $product =Product::find($id);
        $product->product_name=$request->input('product_name');
        $product->product_price=$request->input('product_price');
        $product->product_category =$request->input('product_category');

        if($request->hasFile('product_image')){
                
            // 1 :get filename with ext
            $fileNameWithExt = $request->file('product_image')->getClientOriginalName();

            //2 : get just file name
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

            //3 :get  just extension 
            $extension =$request->file('product_image')->getClientOriginalExtension();

            //4 :file name to store
            $fileNameToStore =$fileName.'_'.time().'.'.$extension;

            //upload image

            $path =$request->file('product_image')->storeAs('puplic/product_image',$fileNameToStore);
            $old_image =Product::find($request->input('id'));

            if($product->product_image != 'noimage.jpg'){
                Storage::delete('public/product_image/'.$product->product_image);
            }
            $product->product_image =$fileNameToStore;
    }
    $product->update();
    return redirect('/products')->with('status' , 'The ' .  $product->product_name .' product has been updated succssefully' );
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteproduct($id)
    {
        $product =Product::find($id);

        if($product->product_image != 'noimage.jpg'){
            Storage::delete('public/product_image/'.$product->product_image);
        }

        $product->delete();
        return redirect('/products')->with('status' , 'The ' .  $product->product_name .' product has been deleted succssefully' );
   
    }
    public function activate_product($id)
    {
       $product=Product::find($id);

       $product->status=1;

       $product->update();

       return redirect('/products')->with('status' , 'The ' .  $product->product_name .' product status has been activated succssefully' );
        
    }
    public function unactivate_product($id)
    {
       $product=Product::find($id);

       $product->status=0;

       $product->update();

       return redirect('/products')->with('status' , 'The ' .  $product->product_name .' product status has been unactivated succssefully' );
        
    }
    public function add_to_cart($id)
    {
        $product =Product::find($id);

        $oldCart =Session::has('cart')? Session::get('cart'):null;
        $cart = new Cart($oldCart);
        $cart->add($product , $id);
        Session::put('cart' ,$cart);

        //dd(Session::get('cart'));
        return redirect::to('/shop');
    }
}

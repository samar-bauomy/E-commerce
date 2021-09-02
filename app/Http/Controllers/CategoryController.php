<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\support\facades\DB;
use App\Category;
use App\Product;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addcategory(Request $request)
    {
        return view('admin.addcategory');
    }


    public function savecategory(Request $request)
    {
        $this->validate($request, ['category_name'=>'required']);
        
       $checkcat = Category::where('category_name' , $request->input('category_name') )->first();

       $category = new Category();

       if (!$checkcat) {
          $category->category_name =$request->input('category_name');
          $category->save();

          return redirect('/addcategory')->with('status' , 'The ' .  $request->category_name .' category has been saved succssefully' );
       } else {
        return redirect('/addcategory')->with('status1' , 'The ' .  $request->input('category_name') .' category  aready exist' );
       }
       
    }

   

    public function categories(Request $request)
    {
        $categories = Category::get();

        return view('admin.categories') ->with('categories' , $categories);
    }

   public function edit($id)
    {
        $category = Category::find($id);

        return view('admin.editcategory')->with('category',$category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatecategory(Request $request, $id)
    {
        $category = Category::find($id);
        $oldcat =$category->category_name;
        $category->category_name= $request->input('category_name');
        

        $data=array();
        $data['product_category'] =$request->input('category_name');

        DB::table('products')
                    ->where('product_category' , $oldcat)
                    ->update($data);

        $category->update();
        return redirect('/categories')->with('status' , 'The ' .  $request->category_name .' category has been updated succssefully' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $category = Category::find($id);


        $category->delete();
        return redirect('/categories')->with('status' , 'The ' .  $category->category_name .' category has been delete succssefully' );
    }

    public function view_by_cat($name)
    {
        $categories =Category::get();
        $products =Product::where('product_category' , $name)->get();

        return view('client.shop')->with('products',$products)->with('categories',$categories);
    }



    public function index()
    {
        $categories = Category::get();
        return response()->json($categories);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Slider;
use App\Product;
use App\Category;
use App\Cart ;
use Stripe\Charge;
use Stripe\Stripe;
use Session;
use App\Order;
use DB;
use App\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class ClientController extends Controller
{
    
    public function index()
    {
        $products = Product::get();
        $sliders = Slider::get();

        return view('client.home')->with('sliders',$sliders)->with('products', $products);
    }

    public function shop()
    {
        $categories=Category::get();
        $products = Product::get();

        return view('client.shop')->with('products', $products)->with('categories',$categories);
    }

    public function cart()
    {
        if(!Session::has('cart')){
            return view('client.cart');
        }

        $oldCart =Session::has('cart')? Session::get('cart'):null;
        $cart = new Cart($oldCart);
        return view('client.cart' ,['products'=> $cart->items]);
       
    }

    public function updateqty(Request $request){

       // print('the product id is '.$request->id. 'and the product qty is '.$request->quantity);

        $oldCart = Session::has('cart')? Session::get('cart'):null;
        $cart = new Cart($oldCart);
        $cart->updateQty($request->id, $request->quantity);
        Session::put('cart', $cart);

        //dd(Session::get('cart'));
        return redirect('/cart');
    }

    public function removeitem($id){

        $oldCart =  Session::has('cart')? Session::get('cart'):null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);

        if(count($cart->items) > 0){
            Session::put('cart' , $cart);

        }else{
            Session::forget('cart');
        }
         //dd(Session::get('cart'));
         return redirect('/cart');

    }

    public function checkout()
    {
        if(!Session::has('client')){
            return redirect('/login');
        }
        if(!Session::has('cart')){
            return redirect('/cart');
        }
        
        return view('client.checkout');
    }

    public function postcheckout(){
        
        if(!Session::has('cart')){
            return redirect('/cart');
        }

       
        //$oldCart = Session::get('cart');
        //$cart = new Cart($oldCart);

        Stripe::setApiKey('sk_test_gHGtV3Z6vh4jCV25BRju3hCv
        ');
        try{
            Charge::create(array(
                "amount" => $cart->totalPrice * 100,
                "currency" => "usd",
                "source" => $request->input('stripeToken'), // obtainded with Stripe.js
                "description" => "Test Charge"
            ));

            $order = new Order();

            $order->name= $request->input('name');
            $order->address= $request->input('address');
            $order->cart= serialize($cart);
            $order->payment_id = $charge->id;

            $order->save();

            $orders = Order::where('payment_id' , $charge->id)->get();

               $orders->transform(function($order , $key){
               $order->cart = unserialize($order->cart);
               return $order;
             });

             $email = Session::get('client')->email;

             Mail::to($email)->send(new SendMail($orders));


        } catch(\Exception $e){
            Session::put('error', $e->getMessage());
            return redirect('/checkout');
        }

        Session::forget('cart');
        
        return redirect('/cart')->with('success' , 'Purchase accomplished successfully !');
    }

    public function login()
    {
        return view('client.login');
    }
    public function signup()
    {
        return view('client.signup');
    }

    public function creataccount(Request $request){
         $this->validate($request,['email' => 'email|required|unique:clients',
                                    'password' => 'required|min:4']);

        $client = new Client();
        $client->email = $request->input('email');                            
        $client->password = bcrypt($request->input('password'));   
        
        $client->save();

        return back()->with('status' , 'your account has been created successfully');

    }

    public function accessaccount(Request $request){
        $this->validate($request,['email' => 'email|required',
                                  'password' => 'required']);

        $client =Client::where('email' , $request->input('email'))->first();
      
        if ($client) {
            # code...
            if (Hash::chech($request->input('password') , $client->password)) {
                # code...
                Session::put('client' , $client);

                return redirect('/shop');
               // return back()->with('status' , 'your email is ' . Session::get('client')->id);

            } else {
                # code...
                return back()->with('error' , 'worng password or email');
            }
            
        } else {
            # code...
            return back()->with('error' , 'your do not have an account ');
        }
        
       }
       public function logout(){
           Session::forget('client');
           return back();
       }

}
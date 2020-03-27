<?php
/**
 * User: gedongdong
 * Date: 2020-03-26 21:49
 */

namespace App\Http\Controllers;


use App\Models\Product;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    public function detail(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return redirect('/');
        }
        $product = Product::find($id);
        if (!$product) {
            return redirect('/');
        }


    }
}
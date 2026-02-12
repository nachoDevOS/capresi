<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    use HasFactory;

    protected $fillable = [
        'vault_id', 'user_id', 'title', 'amount', 'amount_real', 'balance', 'observations', 'status', 'closed_at', 'deleted_at', 'closeUser_id', 'view'
    ];

    public function movements(){
        return $this->hasMany(CashierMovement::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userclose(){
        return $this->belongsTo(User::class, 'closeUser_id');
    }

    public function vault()
    {
        return $this->belongsTo(Vault::class, 'vault_id');
    }

    public function details(){
        return $this->hasMany(CashierDetail::class);
    }


    public function salaryPurchase()
    {
        return $this->hasMany(SalaryPurchase::class, 'cashier_id');
    }
    public function salaryPurchasePayment(){
        return $this->hasMany(SalaryPurchaseMonthAgent::class,'cashier_id');
    }




    public function loan_payments(){
        return $this->hasMany(LoanDayAgent::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }

    public function pawn(){
        return $this->hasMany(PawnRegister::class, 'cashier_id');
    }

    public function pawnPayment(){
        return $this->hasMany(PawnRegisterMonthAgent::class,'cashier_id');
    }

    public function pawnMoneyAditional()
    {
        return $this->hasMany(PawnRegisterAmountAditional::class,'cashier_id');
    }

    public function salePayment(){
        return $this->hasMany(SaleAgent::class);
    }

    //_:::::::::::::
    //Para obtener uno 
    public function vault_details(){
        return $this->hasOne(VaultDetail::class, 'cashier_id');
    }
    //para obtener todoooo
    public function vault_detail(){
        return $this->hasMany(VaultDetail::class, 'cashier_id');
    }
    //_:::::::::::::

    // public function client(){
    //     return $this->hasMany(Client::class);
    // }
}

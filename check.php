DB::table('users')->where('email', 'admin@migestion.com.ar')->update(['id_empresa' => null]);

$user = App\Models\User::find(3);
$user->syncRoles(['superadmin']);

echo 'Listo. Roles actuales: ';
print_r($user->getRoleNames()->toArray());
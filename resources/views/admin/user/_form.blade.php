{!! csrf_field() !!}
<div class="input-group">
  <span class="input-group-addon"><i class="fa fa-user"></i></span>
  <input type="text" class="form-control" name="name" value="{{ old('name',$user->name) }}" placeholder="Digite seu Nome">
</div>
<br>
<div class="input-group">
  <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
  <input type="email" class="form-control" name="email" value="{{ old('email',$user->email) }}" placeholder="Digite seu melhor email">
</div>
<br>
<div class="input-group">
  <span class="input-group-addon"><i class="fa fa-key"></i></span>
  <input type="password" class="form-control" name="password" placeholder="Digite uma Senha">
</div>
<br>
<div class="input-group">
  <span class="input-group-addon"><i class="fa fa-key"></i></span>
  <input type="password" class="form-control" name="password_confirmation" placeholder="Confirme a Senha">
</div>
<br>
<div class="input-group col-lg-2">
  <span class="input-group-addon"><i class="fa fa-group"></i></span>
  <select class="form-control" name="type">
    @foreach($roles as $role)
    <option value="{{$role->id}}">{{$role->name}}</option>
    @endforeach
  </select>
</div>
<br>
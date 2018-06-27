<template>
    <div class="col-md-6">
    <div class="card">
        <div class="card-header">Users</div>
        <div class="card-body" v-if="!saving">
            <div v-if="error">
                <span style="color: red">{{error}}</span>
            </div>
            <div v-if="users && context !=='new'" class="user field is-horizontal">
                <div class="form-group">
                    <button style="max-width: 100px" type="submit" @click="newUser" class="btn btn-primary form-control">New User</button>
                </div>
                <div>
                    <div class="form-group">
                        <label for="selectUser">User</label>
                        <select id="selectUser" class="form-control" v-on:change="onChange" v-model="selectedUserId">
                            <option value="" selected="selected">-- Select user --</option>
                            <option v-for="u in users" v-bind:value="u.id">{{u.name}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div v-if="!users">Loading...</div>
            <div v-if="context =='view' && user">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Name</td>
                            <td>{{user.name}}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>{{user.email}}</td>
                        </tr>
                        <tr>
                            <td>Bonus</td>
                            <td>{{user.percent_bonus}}%</td>
                        </tr>
                        <tr>
                            <td>Country</td>
                            <td>{{country(user.country)}}</td>
                        </tr>
                        <tr>
                            <td>Balance</td>
                            <td>${{user.balance}}</td>
                        </tr>
                    </tbody>
                </table>
                <button type="submit" @click="editUser" class="btn btn-primary">Edit</button>
            </div>
            <div v-if="context =='edit' || context == 'new'">
                <form>
                    <div class="form-group">
                        <label for="userName">Name</label>
                        <input type="text" class="form-control" id="userName" placeholder="Enter name" v-model="user.name" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email</label>
                        <input type="text" class="form-control" id="userEmail" placeholder="Enter email" v-model="user.email" required>
                    </div>
                    <div class="form-group" v-if="context != 'new'">
                        <label for="userBonus">Bonus(%)</label>
                        <input type="text" class="form-control" id="userBonus" placeholder="Enter percent bonus" v-model="user.percent_bonus" required>
                    </div>
                    <div class="form-group" v-if="context == 'new'">
                        <label for="userPassword">Password</label>
                        <input type="password" class="form-control" id="userPassword" placeholder="Enter password" v-model="user.password" required>
                    </div>
                    <div class="form-group" v-if="context == 'new'">
                        <label for="userPasswordRepeat">Repeat Password</label>
                        <input type="password" class="form-control" id="userPasswordRepeat" placeholder="Repeat password" v-model="user.password2" required>
                    </div>
                    <div class="form-group">
                        <label for="userCountry">Country</label>
                        <select id="userCountry" class="form-control" v-model="user.country" required>
                            <option v-for="(c, index) in countries()" v-bind:value="index" v-bind:selected="index == user.country">{{c}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userBalance">Balance ($)</label>
                        <input type="text" class="form-control" id="userBalance" placeholder="Enter balance" v-model="user.balance">
                    </div>
                    <button type="submit" class="btn btn-primary" @click="onSave">Save</button>
                    <button type="submit" class="btn btn-primary" @click="onCancel">Cancel</button>
                </form>
            </div>
        </div>
        <div class="card-body" v-if="saving">
            <span><i>Saving...</i></span>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                users: null,
                user: null,
                selectedUserId: null,
                context: null,
                saving: false,
                error: null
            };
        },
        methods:{
            onChange(e){
                this.context = 'view';
                this.user = this.users.find((user) => user.id == this.selectedUserId);
            },
            country(code){
                return this.countries()[code];
            },
            countries(){
                return window.bet360.countries;
            },
            newUser(){
                this.context = 'new';
                this.user = {
                    balance:null,
                    country:"",
                    email:"",
                    name:"",
                    percent_bonus:5
                };
                this.selectedUserId = null;
            },
            editUser(){
                this.context = 'edit';
            },
            onCancel(){
                if(this.selectedUserId){
                    this.context = 'view';
                }else{
                    this.context = null;
                }
            },
            onSave(){
                this.context = null;
                this.saving = true;
                this.error = null;

                //UPDATE EXISTING USER RECORD
                if(this.selectedUserId){
                    window.axios.post('/api/user', this.user)
                    .then((response) => {
                        //console.log(response.data);
                        this.saving = false;
                        this.loadUsers();
                        this.context = 'view';
                    })
                    .catch((error) => {
                        //console.log(error.response);
                        this.error = error.response.data.message;
                        this.saving = false;
                        this.context = 'edit';
                    });
                }
                //CREATE NEW USER
                else{
                    window.axios.put('/api/user', this.user)
                    .then((response) => {
                        this.saving = false;
                        this.selectedUserId = response.data.id;
                        this.loadUsers();
                        this.context = 'view';
                    })
                    .catch((error) => {
                        console.log(error.response);
                        this.error = error.response.data.message;
                        this.saving = false;
                        this.context = 'new';
                    });
                }
            },
            loadUsers(){
                this.error = null;
                window.axios.get('/api/users')
                .then((response) => {
                    this.users = response.data;
                })
                .catch((error) => {
                    //console.log(error.response);
                    this.error = error.response.data.message;
                });
            }
        },
        mounted()
        {
            this.loadUsers();
        }
    }
</script>
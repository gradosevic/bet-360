<template>
    <div class="col-md-12">
    <div class="card">
        <div class="card-header">Transactions</div>
        <div class="card-body">
            <div v-if="error">
                <span style="color: red">{{error}}</span>
            </div>
            <div class="filters">
                <label for="from">From:</label>
                <input type="text" id="from" class="form-control">
                <label for="to">To:</label>
                <input type="text" id="to" class="form-control">
                <div v-if="users" class="user field">
                    <div class="form-group">
                        <label for="selectUser">User:</label>
                        <select id="selectUser" class="form-control" v-model="selectedUserId">
                            <option value="" selected="selected">-- All users --</option>
                            <option v-for="u in users" v-bind:value="u.id">{{u.name}}</option>
                        </select>
                    </div>
                </div>
                <br/>
                <button type="submit" class="btn btn-primary" @click="go">Go</button>
            </div>
            <br/>
            <div v-if="transaction">
                <table class="table">
                    <tbody>
                    <tr>
                        <td>ID</td>
                        <td>{{transaction.name}}</td>
                    </tr>
                    <tr>
                        <td>User</td>
                        <td>{{userById(transaction.user_id).name}}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{transaction.date}}</td>
                    </tr>
                    <tr>
                        <td>Country</td>
                        <td>{{country(transaction.country)}}</td>
                    </tr>
                    <tr>
                        <td>Type</td>
                        <td>{{transaction.type == TYPE_WITHDRAWAL?'WITHDRAWAL':'DEPOSIT'}}</td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td>${{transaction.amount}}</td>
                    </tr>
                    <tr>
                        <td>Created at</td>
                        <td>${{transaction.updated_at}}</td>
                    </tr>
                    <tr>
                        <td>Updated at</td>
                        <td>${{transaction.created_at}}</td>
                    </tr>
                    </tbody>
                </table>
                <button type="submit" @click="transaction=null" class="btn btn-primary">Back</button>
            </div>
            <div v-if="!transaction && transactions && !loading" class="transaction">
                <span v-if="!transactions.length">No transactions found.</span>
                <table class="table " v-if="transactions.length">
                    <thead>
                        <tr>
                            <th><abbr title="User">ID</abbr></th>
                            <th><abbr title="User">User</abbr></th>
                            <th><abbr title="Transaction date">Date</abbr></th>
                            <th><abbr title="Transaction from/to country">Country</abbr></th>
                            <th>TYPE</th>
                            <th><abbr title="Transaction amount">Amount</abbr></th>
                        </tr>
                        </thead>
                    <tbody v-for="t in transactions" >
                        <tr>
                            <td><a href="#" @click="showTransaction(t.id)">{{t.id}}</a></td>
                            <td>{{t.user.name}}</td>
                            <td>{{t.date}}</td>
                            <td>{{country(t.country)}}</td>
                            <td>{{t.type == TYPE_WITHDRAWAL?'WITHDRAWAL':'DEPOSIT'}}</td>
                            <td>${{t.type == TYPE_WITHDRAWAL?'-':''}}{{t.amount}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="loading">Loading...</div>
        </div>
    </div>
        </div>
</template>

<script>
    export default {
        data() {
            return {
                transactions: null,
                transaction: null,
                users: null,
                selectedUserId: null,
                user: null,
                loading: false,
                error: null
            };
        },
        methods:{
            onChange(e){
                //this.user = this.users.find((user) => user.id == this.selectedUserId);
            },
            country(code){
                return window.bet360.countries[code];
            },
            go(){
                this.loading = true;
                window.axios.post('/api/transactions', {
                    from: $( "#from").val(),
                    to: $( "#to").val(),
                    user_id: this.selectedUserId
                })
                .then((response) => {
                    //console.log(response.data);
                    this.transactions = response.data;
                    this.loading = false;
                });
            },
            showTransaction(tid){
                this.loading = true;
                window.axios.get('/api/transaction/' + tid)
                .then((response) => {
                    this.transaction = response.data;
                    this.loading = false;
                });
            },
            userById(user_id){
                return this.users.find((user) => user.id == user_id);
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
        created(){
            this.TYPE_DEPOSIT = 1;
            this.TYPE_WITHDRAWAL = 2;
        },
        mounted()
        {
            this.loadUsers();
            $( function() {
                $( "#from" ).datepicker({ dateFormat: 'yy-mm-dd' });
                $( "#to" ).datepicker({ dateFormat: 'yy-mm-dd' });
            } );
        }
    }
</script>
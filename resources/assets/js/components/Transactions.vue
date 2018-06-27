<template>
    <div class="col-md-12">
    <div class="card">
        <div class="card-header">Transactions</div>
        <div class="card-body">
            <div v-if="transactions" class="transaction">
                <table class="table ">
                    <thead>
                        <tr>
                            <th><abbr title="User">User</abbr></th>
                            <th><abbr title="Transaction date">Date</abbr></th>
                            <th><abbr title="Transaction from/to country">Country</abbr></th>
                            <th>TYPE</th>
                            <th><abbr title="Transaction amount">Amount</abbr></th>
                        </tr>
                        </thead>
                    <tbody v-for="t in transactions" >
                        <tr>
                            <td>{{t.user.name}}</td>
                            <td>{{t.date}}</td>
                            <td>{{country(t.country)}}</td>
                            <td>{{t.type == TYPE_WITHDRAWAL?'WITHDRAWAL':'DEPOSIT'}}</td>
                            <td>${{t.type == TYPE_WITHDRAWAL?'-':''}}{{t.amount}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!transactions">Loading...</div>
        </div>
    </div>
        </div>
</template>

<script>
    export default {
        data() {
            return {
                transactions: null
            };
        },
        methods:{
          country(code){
                return window.bet360.countries[code];
            }
        },
        created(){
            this.TYPE_DEPOSIT = 1;
            this.TYPE_WITHDRAWAL = 2;
        },
        mounted()
        {
            window.axios.get('/api/transactions')
                .then((response) => {
                    console.log(response.data);
                    this.transactions = response.data;
                });
        }
    }
</script>
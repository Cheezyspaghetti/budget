
{{--<table class="table table-bordered">--}}
    {{--<tbody v-for="transaction in autocomplete.transactions" v-bind:style="{color: colors[transaction.type]}" v-on:mousedown="autocompleteTransaction(transaction)" class="pointer">--}}

    {{--<tr>--}}
        {{--<!-- <th>date</th> -->--}}
        {{--<th>description</th>--}}
        {{--<th>merchant</th>--}}
        {{--<th>total</th>--}}
        {{--<th>type</th>--}}
        {{--<th>account</th>--}}
        {{--<th>from</th>--}}
        {{--<th>to</th>--}}
    {{--</tr>--}}

    {{--<tr v-bind:class="{'selected': transaction.selected}" class="">--}}
        {{--<!-- <td>[[transaction.date]]</td> -->--}}
        {{--<td class="description">[[transaction.description]]</td>--}}
        {{--<td class="merchant">[[transaction.merchant]]</td>--}}
        {{--<td class="total">[[transaction.total]]</td>--}}
        {{--<td class="type">[[transaction.type]]</td>--}}
        {{--<td data-id="[[transaction.account.name]]" class="account">[[transaction.account.name]]</td>--}}
        {{--<td>[[transaction.from_account.name]]</td>--}}
        {{--<td>[[transaction.to_account.name]]</td>--}}
    {{--</tr>--}}

    {{--<tr>--}}
        {{--<td colspan="7">--}}
            {{--<li v-for="tag in transaction.tags" v-bind:class="{'tag-with-budget': tag.budget !== null || tag.percent !== null, 'tag-without-budget': tag.budget === null || tag.percent === null}" class="label label-default" data-id="[[tag.id]]" data-allocated-percent="[[tag.allocated_percent]]" data-allocated-fixed="[[tag.allocated_fixed]]" data-amount="[[tag.amount]]">[[tag.name]]</li>--}}
        {{--</td>--}}
        {{--<td colspan="1" class="budget-tag-info">[[transaction.allocate]]</td>--}}
    {{--</tr>--}}

    {{--</tbody>--}}
{{--</table>--}}
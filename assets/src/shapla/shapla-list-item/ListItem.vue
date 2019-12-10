<template>
    <div class="shapla-list-item">
        <span class="shapla-list-item-label">{{label}}</span>
        <span class="shapla-list-item-separator">:</span>
        <span class="shapla-list-item-value"><slot>{{value}}</slot></span>
    </div>
</template>

<script>
    export default {
        name: "ListItem",
        props: {
            label: {type: String, required: false},
            value: {type: String, required: false},
        }
    }
</script>

<style lang="scss">
    .shapla-list-item {
        align-items: center;
        display: flex;
        justify-content: flex-start;

        &:not(:last-child) {
            margin-bottom: 5px;
            padding-bottom: 5px;
        }

        &-label {
            max-width: 130px;
            width: 30%;
        }

        &-separator {
            text-align: center;
            width: 20px;
        }
    }
</style>

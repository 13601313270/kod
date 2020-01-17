{php}

{/php}
<script>
setTimeout(() => {
    fetch('/hotModule')
        .then(res => res.json())
        .then(json => console.log(json))
    console.log(1);
}, 2000);
</script>

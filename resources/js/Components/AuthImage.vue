<template>
    <img 
        :src="'/' + $page.props.auth.user.profile_image" 
        alt="User Photo" 
    />
</template>
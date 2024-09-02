<template>
    <div class="user-profile">
        <img :src="userPicture" alt="Profile Picture" class="profile-pic" />
        <span class="user-name">{{ userName }}</span>
    </div>
    <div>
        <select v-model="selectedPageToken">
            <option v-for="page in pages" :key="page.id" :value="page.access_token">{{ page.name }}</option>
        </select>
        <button @click="getInsights">Submit</button>

        <div v-if="insights">
            <h3>Page Insights</h3>
            <p>Total Followers/Fans: {{ insights.page_follows }}</p>
            <p>Total Engagement: {{ insights.page_post_engagements }}</p>
            <p>Total Impressions: {{ insights.page_impressions }}</p>
            <p>Total Reactions: {{ insights.page_total_actions }}</p>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        pages: Array,
        token: String,
        userPicture: String,
        userName : String,
    },
    data() {
        return {
            selectedPageToken: null,
            insights: null,
        };
    },
    methods: {
        async getInsights() {
            try {
                const response = await axios.post('/facebook/fetch-insights', {
                    page_id: this.getSelectedPageId(),
                    page_token: this.selectedPageToken,
                    token: this.token,
                });
                this.insights = this.extractInsights(response.data);
            } catch (error) {
                console.error('Error fetching insights:', error);
            }
        },
        getSelectedPageId() {
            const selectedPage = this.pages.find(page => page.access_token === this.selectedPageToken);
            return selectedPage ? selectedPage.id : null;
        },
        extractInsights(data) {
            // Extract 'page_follows' metric from the response data
            const insights = {};
            data.data.forEach(metric => {
                
                if (metric.name === 'page_fans') {
                    // Assuming you want the most recent value
                    console.log("metric value" + metric.values[metric.values.length - 1].value);
                    const recentValue = metric.values[metric.values.length - 1].value;
                    insights.page_follows = recentValue;
                } else if (metric.name === 'page_post_engagements') {
                    // Take the most recent value
                    insights.page_post_engagements = metric.values[metric.values.length - 1].value;
                }
                else if (metric.name === 'page_impressions') {
                    insights.page_impressions = metric.values[metric.values.length - 1].value;
                } else if (metric.name === 'page_actions_post_reactions_total') {
                    let reactionValues = metric.values[metric.values.length - 1].value;
                    insights.page_total_actions = Array.isArray(reactionValues) && reactionValues.length > 0
                        ? reactionValues.reduce((a, b) => a + b, 0)
                        : 0;
                }
            });
            return insights;
        },
    },
};
</script>
<style scoped>
.user-profile {
    display: flex;
    align-items: center;
}

.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}

.user-name {
    font-size: 1.2em;
    font-weight: bold;
}
</style>

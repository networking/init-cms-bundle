import Routing from 'fos-router';

export const CMSRouting = {
    instance: null,
    async load(){
        if(!this.instance) {
            this.instance = await this.getRoutes();
        }
        return this.instance;
    },
    async getRoutes(){
        let response = await fetch('/js/routing');
        let data = await response.json();
        Routing.setRoutingData(data);
        return Routing;
    }
}

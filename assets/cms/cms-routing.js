import Routing from 'fos-router';

class CMSRoutingObject {
    constructor() {
        this.instance = null;
    }
    setRouting(routing){
        Routing.setRoutingData(routing);
        this.instance = Routing;
    }
    generate(name, params = {}, absolute = false){
        return this.instance.generate(name, params, absolute);
    }
}

window.CMSRouting = new CMSRoutingObject()



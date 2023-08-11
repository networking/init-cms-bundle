const routes = require('./routing/routing.json');
import Routing from 'fos-router';

Routing.setRoutingData(routes);

const CMSRouting = Routing
export default CMSRouting;
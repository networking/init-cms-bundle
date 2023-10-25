import Translator from "bazinga-translator";

export const CMSTranslator = {
    instance: null,
    async load(){
        if(!this.instance) {
            this.instance = await this.getRoutes();
        }
        return this.instance;
    },
    async getRoutes(){

        let locale = document.documentElement.lang.replace('-', '_');
        let response = await fetch('/js/translations/' + locale + '.json');
        let data = await response.json();
        Translator.fromJSON(data);
        return Translator;
    }
}
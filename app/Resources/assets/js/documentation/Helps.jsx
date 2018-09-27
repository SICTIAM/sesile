import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { AccordionItem } from "../_components/AdminUI"
import DocumentationRow from "./DocumentationRow"
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'

class Helps extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props);
        this.state = {
            helps: []
        };
    }

    componentDidMount() {
        this.fetchHelps()
    }

    fetchHelps () {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallaide'), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(helps => this.setState({helps}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.help_board.title_helps'), errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const {t} = this.context
        const { helps } = this.state
        const row = helps.map((help, key) => <DocumentationRow key={key} documentation={help} download_route="sesile_main_documentationapi_showdocumentaide" />)

        return (
                <div className="cell medium-10">
                    <div className="panel cell medium-10" style={{padding:"1em"}}>
                        <h3>{t('common.help_board.title_helps')}</h3>
                        {row}
                    </div>
                </div>
        )
    }
}

export default translate(['sesile'])(Helps)

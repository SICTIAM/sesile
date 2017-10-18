import React, { Component } from 'react'
import { func, object } from 'prop-types'
import { translate } from 'react-i18next'
import { AccordionItem } from "../_components/AdminUI"
import DocumentationRow from "./DocumentationRow"

class Helps extends Component {
    static contextTypes = {
        t: func
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
        fetch(Routing.generate('sesile_main_documentationapi_getallaides'), { credentials: 'same-origin'})
            .then(this.handleErrors)
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
        console.log(helps)
        const row = helps.map((help, key) => <DocumentationRow key={key} documentation={help} />)

        return (
            <AccordionItem title={t('common.help_board.title_helps')}>
                { row }
            </AccordionItem>
        )
    }
}

export default translate(['sesile'])(Helps)

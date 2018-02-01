import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import { AccordionItem } from "../_components/AdminUI"
import { handleErrors } from '../_utils/Utils'
import DocumentationRow from "./DocumentationRow"


class Patchs extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props);
        this.state = {
            patchs: []
        };
    }

    componentDidMount() {
        this.fetchPatchs()
    }

    fetchPatchs () {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallpatch'), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(patchs => this.setState({patchs}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.help_board.title_patchs'), errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const {t} = this.context
        const { patchs } = this.state
        const row = patchs.map((patch, key) => <DocumentationRow key={key} documentation={patch} download_route="sesile_main_documentationapi_showdocumentpatch" />)

        return (
            <AccordionItem title={t('common.help_board.title_patchs')} className="is-active">
                { row }
            </AccordionItem>
        )
    }
}

export default translate(['sesile'])(Patchs)
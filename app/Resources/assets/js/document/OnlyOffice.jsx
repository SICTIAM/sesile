import React, { Component } from 'react'
import {func, object} from 'prop-types'
import { translate } from 'react-i18next'

class OnlyOffice extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            user: {},
            script: {}
        }
    }

    componentWillReceiveProps(nextProps) {

        if (nextProps.document.id !== this.props.document.id) {
            this.updateOnlyOfficeScript(nextProps.document)

            const modalRoot = document.getElementById('modal-root')
            const el = document.createElement('div')

            el.setAttribute("id", "placeholder")
            modalRoot.appendChild(el)
        }
    }

    componentDidMount () {
        this.updateOnlyOfficeScript(this.props.document)
    }

    updateOnlyOfficeScript (document) {
        const { user } = this.props
        this.setState({
            script: {
                document: {
                    fileType: document.repourl.split('.').pop(),
                    title: document.name,
                    url: Routing.generate('download_jws_doc', {name: document.repourl, token: document.token}, true)
                },
                editorConfig: {
                    callbackUrl: Routing.generate('sesile_document_documentapi_onlyoffice', {id: document.id}, true),
                    customization: {
                        forcesave: true,
                        compactToolbar: true,
                        logo: {
                            image: "http://sesile.fr/images/logos.svg",
                            imageEmbedded: "http://sesile.fr/images/logos.svg",
                            url: "http://sesile.fr"
                        },
                    },
                    lang: "fr-FR",
                    mode: "edit",
                    user: {
                        id: user.id,
                        name: user._prenom + " " + user._nom
                    }
                }
            }
        })
    }

    componentDidUpdate() {
        const { script } = this.state
        script.innerHTML = DocsAPI.DocEditor("placeholder", script)
    }

    render () {
        const { t } = this.context
        return (
            <div className="cell medium-9 height100" key={this.props.document.id} id="modal-root" >
                <div id="placeholder" className="text-center">
                    <h3>{t('common.documents.no_preview')}</h3>
                </div>
            </div>
        )
    }
}

OnlyOffice.propsType = {
    document: object,
    user: object
}

export default translate('sesile')(OnlyOffice)
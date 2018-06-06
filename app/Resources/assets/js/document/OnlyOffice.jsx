import React, { Component } from 'react'
import {func, object, bool} from 'prop-types'
import { translate } from 'react-i18next'

class OnlyOffice extends Component {

    static contextTypes = {
        t: func
    }

    static defaultProps = {
        edit: true
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
                    url: Routing.generate('download_jws_doc', {name: document.repourl, token: document.token}, true),
                    permissions: {
                        edit: this.props.edit
                    },
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
        const { revealDisplay } = this.props
        let className
        revealDisplay
            ? className = "height100"
            : className = "only-office-height"

        return (
            <div className={"cell medium-12 " + className } key={this.props.document.id} id="modal-root" >
                <div className="grid-x" style={{height: '100%'}}>
                    <div className="cell medium-12">
                        <div id="placeholder" className="callout alert" style={{margin: '10px'}}>
                            <h5>{t('common.documents.no_preview')}</h5>
                            <p>{t('common.documents.error.preview')}</p>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

OnlyOffice.propsType = {
    document: object,
    user: object,
    revealDisplay: bool
}

export default translate('sesile')(OnlyOffice)
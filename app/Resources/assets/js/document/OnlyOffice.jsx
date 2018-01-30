import React, { Component } from 'react'
import { object } from 'prop-types'

class OnlyOffice extends Component {

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

        return (
            <div className="cell medium-9 height100" key={this.props.document.id} id="modal-root" >
                <div id="placeholder"></div>
            </div>
        )
    }
}

OnlyOffice.propsType = {
    document: object,
    user: object
}

export default OnlyOffice
import React, { Component } from 'react'
import { object } from 'prop-types'

class OnlyOffice extends Component {

    constructor(props) {
        super(props)
        this.state = {
            script: {
                "document": {
                    "fileType": "",
                    "key": "",
                    "title": "",
                    "token": "",
                    "url": "",
                    "editorConfig": {
                        "callbackUrl": "",
                        "customization": {
                            "logo": {
                                "image": "http://sesile.fr/images/logos.svg",
                                "imageEmbedded": "http://sesile.fr/images/logos.svg",
                                "url": "http://sesile.fr"
                            },
                        }
                    }
                },
            }
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
        console.log(Routing.generate('download_doc', {id: document.id}, true))
        this.setState({
            script: {
                "document": {
                    "fileType": document.repourl.split('.').pop(),
                    "key": document.repourl,
                    "title": document.name,
                    "url": Routing.generate('download_doc', {id: document.id}, true),
                    "editorConfig": {
                        "callbackUrl": Routing.generate('sesile_document_documentapi_onlyoffice', {id: document.id}),
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
    document: object
}

export default OnlyOffice
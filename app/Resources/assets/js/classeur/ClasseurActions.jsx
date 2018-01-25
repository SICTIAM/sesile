import React, { Component } from 'react'
import { func, array, number } from 'prop-types'
import { Textarea } from '../_components/Form'
import { translate } from 'react-i18next'

class ClasseurActions extends Component {
    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
    }

    render() {

        const { actions, addComment } = this.props
        const { t } = this.context

        return (
            <div className="cell medium-12">
                <div className="grid-x grid-margin-x">
                    <h3 className="cell medium-12">{ t('common.classeurs.comments.name') }</h3>
                </div>

                <div className="grid-x grid-margin-x align-top text-center">
                    <div className="cell medium-2 fa fa-comment"></div>
                    <Textarea
                        id="new-action"
                        name="newAction"
                        placeholder="Nouveau commentaire"
                        onChange={addComment}
                        className="cell medium-10"
                    />
                </div>

                { actions && actions.map((action) =>
                    <div className="grid-x grid-margin-x align-top text-center" key={action.id}>
                        <div className="cell medium-2 fa fa-comment"></div>
                        <div className="cell medium-10 text-left">
                            <p>
                                { action.action }
                                <span className="text-bold"> { action.user_action._nom } { action.user_action._prenom }</span>
                            </p>
                        </div>
                    </div>
                )}
            </div>

        )
    }
}

ClasseurActions.propsType = {
    actions: array,
    classeur: number,
    addComment: func
}

export default translate(['sesile'])(ClasseurActions)
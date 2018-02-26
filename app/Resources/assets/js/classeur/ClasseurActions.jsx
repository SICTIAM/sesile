import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import {Button, Textarea} from '../_components/Form'
import { translate } from 'react-i18next'

class ClasseurActions extends Component {
    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
    }

    render() {

        const { actions, action, addComment, submitComment } = this.props
        const { t } = this.context

        return (
            <div className="grid-x panel grid-padding-y">
                <div className="cell medium-12">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <h3 className="cell medium-12">{ t('common.classeurs.comments.name') }</h3>
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x align-top text-center">
                        <div className="cell medium-2"><span className="fa fa-comment"></span></div>
                        <Textarea
                            id="new-action"
                            name="newAction"
                            placeholder={ t('common.classeurs.comments.new') }
                            onChange={addComment}
                            className="cell medium-10"
                            value={action}
                        />
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x grid-margin-bottom align-top text-center">
                        <div className="cell medium-2"></div>
                        <Button labelText={ t('common.classeurs.comments.submit') }
                                className="cell medium-10 text-right"
                                onClick={submitComment}
                        />
                    </div>

                    { actions && actions.map((action) =>
                        <div className="grid-x grid-margin-x grid-padding-x align-top text-center" key={action.id}>
                            <div className="cell medium-2"><span className="fa fa-comment"></span></div>
                            <div className="cell medium-10 text-left">
                                <p>
                                    { action.action }
                                    <span className="text-author"> par
                                        { action.user_action
                                            ? action.user_action._prenom  + " " + action.user_action._nom
                                            : action.username
                                        }</span>
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </div>

        )
    }
}

ClasseurActions.propsType = {
    actions: array,
    action: string,
    classeur: number,
    addComment: func,
    submitComment: func
}

export default translate(['sesile'])(ClasseurActions)
import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { AdminDetails, AccordionContent, AccordionItem} from "../_components/AdminUI"
import Patchs from "./Patchs";
import Helps from "./Helps";

class HelpBoard extends Component {

    static contextTypes = {
        t: func
    }

    componentDidMount() {
        $("#admin-details").foundation()
    }

    render() {
        const { t } = this.context

        return (
            <AdminDetails title={t('common.help_board.title')} nom={t('common.help_board.title')}>
                <AccordionContent>
                    <Helps />
                    <Patchs />
                    <VideoContent />
                </AccordionContent>
            </AdminDetails>
        )
    }

}

export default translate(['sesile'])(HelpBoard)



const VideoContent = ({}, {t}) => {

    return(
        <AccordionItem title={t('common.help_board.title_video')}>
            <div className="medium-12 text-center cell">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/ms-YYoaU4PE?rel=0" frameBorder="0" allowFullScreen></iframe>
            </div>
        </AccordionItem>
    )
}
VideoContent.contextTypes = {
    t: func
}